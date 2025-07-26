<?php

namespace App\Infrastructure\VichUploader;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Naming\NamerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

final class MappingComponent
{
    /** @var array<string, string> */
    private array $mapping;
    private string $prefix;
    private readonly OptionsResolver $resolver;

    /** @param array<string, string> $options */
    public function __construct(
        array $options,
        private readonly StorageInterface $storage,
        private readonly ?NamerInterface $name = null,
        private readonly ?DirectoryNamerInterface $directoryNamer = null,
    ) {
        $this->resolver = new OptionsResolver();
        $this->computeOptions($options);
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /** @return array<string, string> */
    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    public function getName(): ?NamerInterface
    {
        return $this->name;
    }

    public function getDirectoryNamer(): ?DirectoryNamerInterface
    {
        return $this->directoryNamer;
    }

    /** @param array<string, string> $options */
    private function computeOptions(array $options): void
    {
        $resolvedOptions = $this->resolveOptions($options);

        $this->prefix = (string) $resolvedOptions['uri_prefix'];
        $this->mapping = $resolvedOptions;
    }

    /**
     * @param array<string, string> $options
     *
     * @return array<string, string>
     */
    private function resolveOptions(array $options): array
    {
        $this->resolver->setDefined([
            'db_driver',
            'delete_on_remove',
            'delete_on_update',
            'directory_namer',
            'inject_on_load',
            'namer',
            'upload_destination',
        ]);

        $this->resolver->setRequired(['uri_prefix']);
        $this->resolver->setAllowedTypes('uri_prefix', 'string');

        return $this->resolver->resolve($options);
    }
}
