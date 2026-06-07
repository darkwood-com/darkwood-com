<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ArticleTranslation;
use App\Repository\ArticleTranslationRepository;
use App\Service\PageService;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Vich\UploaderBundle\Storage\StorageInterface;

use function in_array;

#[Route(host: '%api_host%')]
class ApiController extends AbstractController
{
    public function __construct(
        private readonly CommonController $commonController,
        private readonly PageService $pageService,
        private readonly UserService $userService,
        private readonly SluggerInterface $slugger,
        private readonly ArticleTranslationRepository $articleTranslationRepository,
        private readonly StorageInterface $storage,
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir
    ) {}

    #[Route(path: ['fr' => '/fr', 'en' => '/', 'de' => '/de'], name: 'api_home', defaults: ['ref' => 'home'])]
    public function home(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('api/pages/home.html.twig', ['page' => $page]);
    }

    #[Route(path: ['fr' => '/fr/mentions-legales', 'en' => '/legal-mentions', 'de' => '/de/impressum'], name: 'api_legal_mention', defaults: ['ref' => 'legal_mention'])]
    public function legalMention(Request $request, $ref)
    {
        return $this->commonController->legalMention($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/plan-du-site', 'en' => '/sitemap', 'de' => '/de/sitemap'], name: 'api_sitemap', defaults: ['ref' => 'sitemap'])]
    public function sitemap(Request $request, $ref)
    {
        return $this->commonController->sitemap($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/sitemap.xml', 'en' => '/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'api_sitemap_xml')]
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }

    #[Route(path: ['fr' => '/fr/rss', 'en' => '/rss', 'de' => '/de/rss'], name: 'api_rss')]
    public function rss(Request $request)
    {
        return $this->commonController->rss($request);
    }

    #[Route(path: ['fr' => '/fr/contact', 'en' => '/contact', 'de' => '/de/kontakt'], name: 'api_contact', defaults: ['ref' => 'contact'])]
    public function contact(Request $request, $ref)
    {
        return $this->commonController->contact($request, $ref);
    }

    #[Route('//api/article-translation/{id}/upload-image', name: 'api_article_translation_upload_image', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function articleTranslationUploadImage(Request $request, int $id): JsonResponse
    {
        // Find the ArticleTranslation
        $articleTranslation = $this->articleTranslationRepository->find($id);
        if ($articleTranslation === null) {
            return new JsonResponse(['error' => 'ArticleTranslation not found'], Response::HTTP_NOT_FOUND);
        }

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('image');

        if (!$uploadedFile) {
            return new JsonResponse(['error' => 'No image file provided'], Response::HTTP_BAD_REQUEST);
        }

        // Validate file type
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes, true)) {
            return new JsonResponse(['error' => 'Invalid file type. Only JPEG, PNG, GIF and WebP are allowed.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Use VichUploader to handle the upload
            $articleTranslation->setImage($uploadedFile);

            // Generate unique filename using the same logic as in BlogArticleService::duplicate
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $this->slugger->slug($originalFilename);
            $fileName = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();
            $articleTranslation->setImageName($fileName);

            // Save the entity to trigger VichUploader
            $this->entityManager->persist($articleTranslation);
            $this->entityManager->flush();

            // Get the uploaded file path
            $imagePath = $this->storage->resolvePath($articleTranslation, 'image');
            $imageUrl = $request->getSchemeAndHttpHost() . '/articles/' . $fileName;

            return new JsonResponse([
                'success' => true,
                'filename' => $fileName,
                'path' => '/articles/' . $fileName,
                'url' => $imageUrl,
            ]);
        } catch (FileException $fileException) {
            return new JsonResponse(['error' => 'Failed to upload file: ' . $fileException->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
