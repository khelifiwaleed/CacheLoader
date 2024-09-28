<?php

namespace CacheLoader\CacheLoaderBundle\Twig;


use CacheLoader\CacheLoaderBundle\Service\TranslationService;
use CacheLoader\CacheLoaderBundle\Service\TranslationObjectService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{

    /**
     * @var TranslationService
     */
    private $translationService;

    /**
     * @var TranslationObjectService
     */
    private $translationObjectService;

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    public function __construct(
        TranslationService $translationService,
        ParameterBagInterface $parameterBag,
        TranslationObjectService $translationObjectService
    ){
        $this->translationService = $translationService;
        $this->parameterBag = $parameterBag;
        $this->translationObjectService = $translationObjectService;
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
            $translationFile = $this->translationObjectService->getTranslationFile();
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
            $filePath = $this->parameterBag->get('trans_dir').$table . '.json';
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