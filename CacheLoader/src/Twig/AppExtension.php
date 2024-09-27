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
            new TwigFunction('cacheLoader', [$this, 'getCacheLoader']),
        ];
    }

    /**
     * @param $param
     * @return array|null
     */
    public function getCacheLoader($param): array|null
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

}