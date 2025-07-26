<?php

namespace App\Infrastructure\VichUploader;

use Doctrine\Common\Proxy\Proxy;
use Psr\Container\ContainerInterface as ContainerBagInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Vich\UploaderBundle\Mapping\Annotation\Uploadable;
use Vich\UploaderBundle\Mapping\Annotation\UploadableField;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

/**
 * @phpstan-type VichConfigService array{service: string, options: array<string>}
 */
final class VichUploaderMappingExtractor
{
    public function __construct(
        private readonly ContainerInterface $container,
        private readonly ContainerBagInterface $parameterBag,
        private readonly StorageInterface $storage,
    ) {
    }

    /**
     * @param object|array<string, mixed> $obj
     */
    public function buildMappingComponent($obj, ?string $fieldName, ?string $className): MappingComponent
    {
        $mapping = $this->getMapping($obj, $fieldName, $className);
        /** @var VichConfigService $namer */
        $namer = $mapping['namer'];
        /** @var VichConfigService $directoryNamer */
        $directoryNamer = $mapping['directory_namer'];

        return new MappingComponent(
            $mapping,
            $this->storage,
            $this->getNamer($namer),
            $this->getDirectoryNamer($directoryNamer),
        );
    }

    /**
     * @param object|array<string, mixed> $obj
     *
     * @return array<string, string>
     */
    public function getMapping($obj, ?string $fieldName, ?string $className): array
    {
        if (!is_object($obj)) {
            throw new \RuntimeException('Cannot get mapping from array');
        }

        $reflection = $this->getReflectionClass($obj);
        if (!$this->isUploadable($reflection)) {
            throw new \RuntimeException('Entity is not uploadable');
        }

        $uploadableField = $this->getUploadableField($reflection, $fieldName, $className);
        $mappingName = $uploadableField->getArguments()['mapping'];
        /** @var array<string, array<string>> $mappings */
        $mappings = $this->parameterBag->get('vich_uploader.mappings');

        if (!\array_key_exists($mappingName, $mappings)) {
            throw new \RuntimeException('VichUploader mapping not found : '.$mappingName.'. Check your configuration at path: vich_uploader.mappings');
        }

        return $mappings[$mappingName];
    }

    /**
     * @param object $obj
     *
     * @phpstan-ignore-next-line
     */
    private function getReflectionClass($obj): \ReflectionClass
    {
        $reflection = new \ReflectionClass($obj);
        if ($this->isDoctrineProxy($reflection->getInterfaceNames())) {
            $parentClass = $reflection->getParentClass();
            if (false !== $parentClass) {
                return $parentClass;
            }
        }

        return $reflection;
    }

    /** @param array<string> $implementedInterfaces */
    private function isDoctrineProxy(array $implementedInterfaces): bool
    {
        return \in_array(Proxy::class, $implementedInterfaces);
    }

    /** @phpstan-ignore-next-line */
    private function isUploadable(\ReflectionClass $reflection): bool
    {
        return 1 >= count($reflection->getAttributes(Uploadable::class));
    }

    /** @phpstan-ignore-next-line */
    private function getUploadableField(\ReflectionClass $reflection, ?string $fieldName, ?string $className): \ReflectionAttribute
    {
        if (null !== $fieldName) {
            $field = $reflection->getProperty($fieldName);
            $attribute = $field->getAttributes(UploadableField::class);
            if (0 === count($attribute)) {
                throw new \RuntimeException('Entity is not uploadable.');
            }
            $attr = array_shift($attribute);

            return $attr;
        }

        $properties = $reflection->getProperties();
        foreach ($properties as $property) {
            if (0 === count($property->getAttributes(UploadableField::class))) {
                continue;
            }

            return $this->getUploadableField($reflection, $property->getName(), $className);
        }
        // If no properties are uploadable
        throw new \RuntimeException('Not uploadable fields on provided class.');
    }

    /**
     * @param VichConfigService $namer
     */
    private function getNamer(array $namer): ?NamerInterface
    {
        /** @var ?NamerInterface $service */
        $service = $this->container->get($namer['service']);
        if ($service) {
            return $service;
        }

        return null;
    }

    /** @param VichConfigService $directoryNamer */
    private function getDirectoryNamer(array $directoryNamer): ?DirectoryNamerInterface
    {
        /** @var ?DirectoryNamerInterface $service */
        $service = $this->container->get($directoryNamer['service']);
        if ($service) {
            return $service;
        }

        return null;
    }
}
