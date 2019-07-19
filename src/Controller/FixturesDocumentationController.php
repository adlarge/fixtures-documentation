<?php
declare(strict_types=1);

namespace Adlarge\FixturesDocumentationBundle\Controller;

use Exception;
use Adlarge\FixturesDocumentationBundle\Service\FixturesDocumentationManager;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FixturesDocumentationController extends AbstractController
{
    /**
     * @var FixturesDocumentationManager
     */
    private $documentationManager;
    /**
     * @var string
     */
    private $docTitle;
    /**
     * @var array
     */
    private $reloadCommands;

    /**
     * FixtureDocumentationController constructor.
     *
     * @param FixturesDocumentationManager $documentationManager
     * @param string $docTitle
     * @param array $reloadCommands
     */
    public function __construct(
        FixturesDocumentationManager $documentationManager,
        string $docTitle,
        array $reloadCommands
    ) {
        $this->documentationManager = $documentationManager;
        $this->docTitle = $docTitle;
        $this->reloadCommands = $reloadCommands;
    }

    /**
     * @return Response
     */
    public function generateDocumentationAction(): Response
    {
        return $this->render(
            '@AdlargeFixturesDocumentation/fixtures.documentation.html.twig',
            [
                'doc' => $this->documentationManager->getDocumentation(),
                'docTitle' => $this->docTitle,
                'canReload' => !empty($this->reloadCommands)
            ]
        );
    }

    /**
     * Reload fixtures.
     *
     * @return Response
     *
     * @throws Exception
     */
    public function reloadAction(): Response
    {
        try {
            $this->documentationManager->reload();
        } catch (RuntimeException $e) {
            return new JsonResponse(
                ['error' => 'An error occurred.'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse();
    }
}
