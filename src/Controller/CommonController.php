<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact;
use App\Entity\Page;
use App\Entity\PageTranslation;
use App\Form\ContactType;
use App\Services\ContactService;
use App\Services\PageService;
use App\Services\SeoService;
use App\Services\SiteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use Twig\Environment;

class CommonController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly PageService $pageService,
        private readonly SiteService $siteService,
        private readonly ContactService $contactService,
        private readonly SeoService $seoService,
        private readonly HtmlErrorRenderer $errorRenderer
    ) {}

    /**
     * Remove slash and redirect 301.
     */
    public function removeTrailingSlash(Request $request): RedirectResponse
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();
        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Show Exception action.
     */
    public function showException(Request $request, Throwable $exception, ?DebugLoggerInterface $logger = null)
    {
        if (!$exception instanceof NotFoundHttpException) {
            $exception = $this->errorRenderer->render($exception);

            return new Response($exception->getAsString(), $exception->getStatusCode(), $exception->getHeaders());
        }

        $host = $request->getHost();
        $site = $this->siteService->findOneByHost($host);
        if ($site === null) {
            $site = $this->siteService->findOneToEdit($this->container->get('request_stack')->getSession()->get('siteId'));
            if (!$site) {
                $site = $this->siteService->findOneByRef('darkwood');
            }
        }

        $page = new Page();
        $page->setRef('404');
        $page->setSite($site);

        $pageTranslation = new PageTranslation();
        $pageTranslation->setTitle('404');
        $pageTranslation->setLocale($request->getLocale());

        $page->addTranslation($pageTranslation);
        $response = $this->render('common/pages/404.html.twig', ['page' => $pageTranslation, 'site_ref' => $site->getRef()]);
        $response->setStatusCode(Response::HTTP_NOT_FOUND);

        return $response;
    }

    public function seo($context)
    {
        return $this->render('common/partials/seo.html.twig', ['data' => $this->seoService->getSeo($context)]);
    }

    public function hreflangs(Request $request, $ref, $entity)
    {
        $canonical = $this->pageService->getPageCanonical($ref, $entity, $request->getHost());
        $pageLinks = $this->pageService->getPageLinks($ref, $entity, $request->getHost(), $request->getLocale(), UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->render('common/partials/hreflangs.html.twig', ['canonical' => $canonical, 'pageLinks' => $pageLinks]);
    }

    /**
     * @param string $ref
     */
    public function getPage(Request $request, $ref): PageTranslation
    {
        $page = $this->pageService->findOneActiveByRefAndHost($ref, $request->getHost());
        if ($page === null) {
            throw $this->createNotFoundException('Page not found !');
        }

        return $page->getOneTranslation($request->getLocale());
    }

    public function legalMention(Request $request, $ref)
    {
        $page = $this->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();

        return $this->render('common/pages/legalMention.html.twig', ['page' => $page, 'site_ref' => $siteRef]);
    }

    public function sitemap(Request $request, $ref)
    {
        $page = $this->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();
        $sitemap = $this->siteService->getSitemap($request->getHost(), $request->getLocale());

        return $this->render('common/pages/sitemap.html.twig', ['page' => $page, 'sitemap' => $sitemap, 'site_ref' => $siteRef]);
    }

    public function sitemapXml(Request $request)
    {
        $sitemapXml = $this->siteService->getSitemapXml($request->getHost(), $request->getLocale());
        $response = new Response($sitemapXml);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }

    public function rss(Request $request)
    {
        $rssXml = $this->siteService->getRssXml($request->getHost(), $request->getLocale());
        $response = new Response($rssXml);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }

    public function contact(Request $request, $ref)
    {
        $page = $this->getPage($request, $ref);
        $siteRef = $page->getPage()->getSite()->getRef();
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $contact->setUser($this->getUser());
                $this->contactService->save($contact);
                $this->container->get('request_stack')->getSession()->getFlashBag()->add('success', $this->translator->trans('common.contact.submited'));

                try {
                    $this->sendMail('common/mails/contact.html.twig', ['contact' => $contact], 'mathieu@darkwood.fr' /* $contact->getEmail() */, 'mathieu@darkwood.fr');
                    $contact->setEmailSent(true);
                } catch (TransportException) {
                    $contact->setEmailSent(false);
                }

                $this->contactService->save($contact);

                return $this->redirectToRoute($siteRef . '_contact', ['ref' => $ref]);
            }
        }

        return $this->render('common/pages/contact.html.twig', ['form' => $form, 'page' => $page, 'site_ref' => $siteRef]);
    }

    /**
     * @param string $templateName
     * @param array  $context
     * @param string $fromEmail
     * @param string $toEmail
     *
     * @throws Throwable
     */
    private function sendMail($templateName, $context, $fromEmail, $toEmail): void
    {
        $template = $this->twig->load($templateName);
        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);
        $message = (new Email())->from($fromEmail)->to($toEmail)->subject($subject);
        if ($htmlBody !== '') {
            $message->html($htmlBody)->text($textBody);
        } else {
            $message->html($textBody);
        }

        $this->mailer->send($message);
    }
}
