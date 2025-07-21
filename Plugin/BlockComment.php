<?php
namespace Networld\Debug\Plugin;

use Magento\Cms\Block\Block as CmsBlock;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class BlockComment
{
    /** @var BlockRepositoryInterface */
    protected $blockRepository;

    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    /** @var LoggerInterface */
    protected $logger;

    /** XML path to enable debug hints */
    const XML_PATH_DEBUG_HINTS_ENABLED = 'networld_debug/general/enabled';

    /**
     * Constructor
     *
     * @param BlockRepositoryInterface $blockRepository
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        BlockRepositoryInterface $blockRepository,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->blockRepository = $blockRepository;
        $this->scopeConfig     = $scopeConfig;
        $this->logger          = $logger;
    }

    /**
     * Around plugin for toHtml(): wraps output with debug comments, logs block load order/timing,
     * and on shutdown outputs an aggregated summary of block loads.
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundToHtml(
        \Magento\Framework\View\Element\AbstractBlock $subject,
        callable $proceed
    ) {
        // Only run when enabled in config
        if (!$this->scopeConfig->isSetFlag(
            self::XML_PATH_DEBUG_HINTS_ENABLED,
            ScopeInterface::SCOPE_STORE
        )) {
            return $proceed();
        }

        // Static trackers
        static $handlesLogged      = false;
        static $sequence           = 0;
        static $summaryRegistered  = false;
        static $summaryData        = [];

        // On first block, log handles and register shutdown for summary
        if (!$handlesLogged) {
            $handles = $subject->getLayout()
                ->getUpdate()
                ->getHandles();
            $this->logger->debug(
                'DebugHints: layout handles = ' . implode(', ', $handles)
            );
            // Register shutdown summary
            if (!$summaryRegistered) {
                register_shutdown_function(function() use (&$summaryData) {
                    if (empty($summaryData)) {
                        return;
                    }
                    $totalBlocks = count($summaryData);
                    $totalTime   = array_sum(array_column($summaryData, 'duration'));
                    $avgTime     = $totalTime / $totalBlocks;
                    // log summary
                    $this->logger->debug(
                        sprintf(
                            'DebugHints Summary: %d blocks, total %.5f sec, average %.5f sec',
                            $totalBlocks,
                            $totalTime,
                            $avgTime
                        )
                    );
                    // find top 5 slowest
                    usort($summaryData, function($a, $b) {
                        return $b['duration'] <=> $a['duration'];
                    });
                    $top = array_slice($summaryData, 0, 5);
                    foreach ($top as $idx => $item) {
                        $this->logger->debug(
                            sprintf(
                                'DebugHints Top %d: %s (%s) - %.5f sec',
                                $idx + 1,
                                $item['name'],
                                $item['class'],
                                $item['duration']
                            )
                        );
                    }
                });
                $summaryRegistered = true;
            }
            $handlesLogged = true;
        }

        // Track sequence and timing
        $sequence++;
        $start    = microtime(true);
        $html     = $proceed();
        $duration = microtime(true) - $start;

        // Determine block name
        $blockName = $subject->getNameInLayout() ?: 'Unnamed';
        if ($subject instanceof CmsBlock) {
            try {
                $cmsBlock = $subject->getCmsBlock();
                if (!$cmsBlock || !$cmsBlock->getId()) {
                    $blockId = $subject->getBlockId();
                    if ($blockId) {
                        $cmsBlock = $this->blockRepository->getById($blockId);
                    }
                }
                if ($cmsBlock && $cmsBlock->getIdentifier()) {
                    $blockName = $cmsBlock->getIdentifier();
                }
            } catch (\Exception $e) {
                // ignore
            }
        }
        $blockClass = get_class($subject);

        // Log each block
        $this->logger->debug(
            sprintf(
                'DebugHints: [%d] %s (%s) - %.5f sec',
                $sequence,
                $blockName,
                $blockClass,
                $duration
            )
        );

        // Save to summary
        $summaryData[] = [
            'name'     => $blockName,
            'class'    => $blockClass,
            'duration' => $duration,
        ];

        // Wrap with comments
        $commentStart = sprintf(
            "<!-- START BLOCK [%d]: %s (%s) -->\n",
            $sequence,
            $blockName,
            $blockClass
        );
        $commentEnd   = sprintf(
            "\n<!-- END BLOCK [%d]: %s (%s) (%.5f sec) -->",
            $sequence,
            $blockName,
            $blockClass,
            $duration
        );

        return $commentStart . $html . $commentEnd;
    }
}