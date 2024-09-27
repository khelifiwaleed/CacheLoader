<?php


namespace CacheLoader\CacheLoaderBundle\Service;

use ReflectionClass;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class TranslationService
 *
 * @package App\Service\Site
 */
class TranslationService
{
    /**
     * @var ParameterBagInterface
     */
    private $parameterBag;

    private $connection;

    private $filesystem;
    /**
     * AppExtension constructor.
     *
     * @param ParameterBagInterface $parameterBag
     */
    public function __construct(
        ParameterBagInterface $parameterBag,
        Connection $connection,
        Filesystem $filesystem
    ){
        $this->parameterBag = $parameterBag;
        $this->connection = $connection;
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

    public function createContentFile (array $array, $fileName): JsonResponse
    {
        foreach ($array as $item){
            $entityArray[] = $this->convert($item);
        }
        try {
            $filePath = __DIR__ . '/../../../../var/translations/' . $fileName . '.json';
            $jsonData = json_encode(array_shift($entityArray));
            $this->filesystem->dumpFile($filePath, $jsonData);
            return new JsonResponse(['message' => 'Données exportées avec succès dans ' . $filePath]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

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




    public function exportTableToJson(string $tableName): JsonResponse
    {
        try {
            $filePath = __DIR__ . '/../../../../var/translations/' . $tableName . '.json';
            $this->saveToJsonFile($tableName, $filePath);

            return new JsonResponse(['message' => 'Données exportées avec succès dans ' . $filePath]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    public function saveToJsonFile(string $tableName, string $filePath): void
    {
        $data = $this->getAllValuesFromTable($tableName);
        $jsonData = json_encode($data);
        $this->filesystem->dumpFile($filePath, $jsonData);
    }

    public function getAllValuesFromTable($tableName)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new \InvalidArgumentException('Nom de table invalide.');
        }
        $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($tableName)
            ->fetchAllAssociative();

        return $result;
    }


    public function getCacheTable($tableName): ?array
    {
        $contentJson = $this->getFileContentTable($tableName);
        if ($contentJson === null) {
            return null;
        }
        return json_decode($contentJson, true);
    }

    /**
     * @return string|null
     */
    public function getFileContentTable($tableName): ?string
    {
        $file = $this->parameterBag->get('trans_dir').$tableName.'.json';
        if (file_exists($file) === false) {
            return null;
        }
        return file_get_contents($file);
    }

}
