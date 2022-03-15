<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait WithQueryParamsRedirectTrait
{
    private UrlGeneratorInterface $urlGenerator;

    #[Required]
    final public function setUrlGenerator(UrlGeneratorInterface $urlGenerator): void
    {
        $this->urlGenerator = $urlGenerator;
    }

    /** @param array<string, string> $queryParams */
    final public function redirectToRouteWithQueryParams(string $route, array $queryParams): Response
    {
        return $this->redirect($this->urlGenerator->generate($route, $queryParams));
    }

    /** @param array<string, string> $queryParams */
    final public function getUrl(string $route, array $queryParams): string
    {
        return $this->urlGenerator->generate($route, $queryParams);
    }
}
