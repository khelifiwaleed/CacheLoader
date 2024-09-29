<?php


namespace CacheLoader\CacheLoaderBundle\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\DBAL\Connection;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;


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

    /**
     * @var Connection
     */
    private $connection;

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
        Connection $connection,
        Filesystem $filesystem
    ){
        $this->parameterBag = $parameterBag;
        $this->connection = $connection;
        $this->filesystem = $filesystem;
    }

 
    /**
     * @return JsonResponse
     */
    public function exportTableToJson(string $tableName): JsonResponse
    {
        try {
            $filePath = $this->parameterBag->get('trans_dir').$tableName.'.json';
            $this->saveToJsonFile($tableName, $filePath);

            return new JsonResponse(['message' => 'Données exportées avec succès dans ' . $filePath]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @return void
     */
    public function saveToJsonFile(string $tableName, string $filePath): void
    {
        $data = $this->getAllValuesFromTable($tableName);
        $jsonData = json_encode($data);
        $this->filesystem->dumpFile($filePath, $jsonData);
    }

    /**
     * @return array|null
     */
    public function getAllValuesFromTable($tableName): ?array
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
            throw new \InvalidArgumentException('Invalid tabel name.');
        }
        try{
            $result = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($tableName)
            ->fetchAllAssociative();
        }catch(\EXCEPTION $e){
            throw new \InvalidArgumentException($e);
        }
        return $result;
    }

    /**
     * @return array|null
     */
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
