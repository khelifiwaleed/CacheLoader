<?php

namespace CacheLoader\CacheLoaderBundle\Twig;


use CacheLoader\CacheLoaderBundle\Service\TranslationService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{

    /**
     * @var TranslationService
     */
    private $translationService;

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('cacheLoaderObject', [$this, 'getCacheLoaderObject']),
            new TwigFunction('cacheLoader', [$this, 'getCacheLoaderTable']),
        ];
    }

    /**
     * @param $param
     * @return array|null
     */
    public function getCacheLoaderObject($param): array|null
    {
        try {
            $translationFile = $this->translationService->getTranslationFile();
            $cacheLoader = array_filter($translationFile, function($item) use($param){
                return $item['LABEL'] === $param ? $item: [];
            });
        } catch(\Exception $e) {
            return null;
        }
        return array_shift($cacheLoader);
    }

    /**
     * @param $param
     * @return array|null
     */
    public function getCacheLoaderTable($table, $param): array|null
    {
        try {
            $filePath = __DIR__ . '/../../../../var/translations/' . $table . '.json';
            if (false === file_exists($filePath)) {
                $this->translationService->exportTableToJson($table);
            }
            $translationFile = $this->translationService->getCacheTable($table);
            $cacheLoader = array_filter($translationFile, function($item) use($param){
                return $item['id'] === (int) $param ? $item: [];
            });
        } catch(\Exception $e) {
            return null;
        }
        return array_shift($cacheLoader);
    }

}