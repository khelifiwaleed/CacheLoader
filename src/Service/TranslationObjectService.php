<?php

namespace CacheLoader\CacheLoaderBundle\Service;


use ReflectionClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Class TranslationObjectService
 *
 * @package App\Service\Site
 */
class TranslationObjectService
{

    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * AppExtension constructor.
     *
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        ParameterBagInterface $parameterBag,
        Filesystem $filesystem
    ){
        $this->parameterBag = $parameterBag;
        $this->filesystem = $filesystem;
    }


    /**
     * @return array|null
     */
    public function getTranslationFile(): ?array
    {
        $contentJson = $this->getFileContent();
        if ($contentJson === null) {
            return null;
        }
        return json_decode($contentJson, true);
    }

    /**
     * @return string|null
     */
    public function getFileContent(): ?string
    {
        $file = $this->parameterBag->get('trans_dir').'translation.json';
        if (file_exists($file) === false) {
            return null;
        }
        return file_get_contents($file);
    }

    /**
     * @return JsonResponse
     */
    public function createContentFile (array $array, $fileName): JsonResponse
    {
        foreach ($array as $item){
            $entityArray[] = $this->convert($item);
        }
        try {
            $filePath = $this->parameterBag->get('trans_dir').$fileName . '.json';
            $jsonData = json_encode(array_shift($entityArray));
            $this->filesystem->dumpFile($filePath, $jsonData);
            return new JsonResponse(['message' => 'Données exportées avec succès dans ' . $filePath]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @return array
     */
    public function convert(object $object): array
    {
        $reflection = new ReflectionClass($object);
        $properties = $reflection->getProperties();
        $data = [];
        foreach ($properties as $property) {
            $data[$property->getName()] = $property->getValue($object);
        }

        return $data;
    }
}