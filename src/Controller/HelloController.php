<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Contact;
use App\Form\ContactType;
use App\Services\ArticleService;
use App\Services\ContactService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use Twig\Environment;

#[Route('/', name: 'hello_', host: '%hello_host%')]
class HelloController extends AbstractController
{
    public function __construct(
        private readonly Environment $twig,
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly CommonController $commonController,
        private readonly ContactService $contactService,
        private readonly ArticleService $articleService,
        private readonly CsrfTokenManagerInterface $tokenManager
    ) {}

    #[Route(path: ['fr' => '/fr', 'en' => '/', 'de' => '/de'], name: 'home', defaults: ['ref' => 'home'])]
    public function home(Request $request, $ref)
    {
        $page = $this->commonController->getPage($request, $ref);
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

                return $this->redirectToRoute('hello_home', ['ref' => 'contact']);
            }
        }

        $articles = $this->articleService->findActives($request->getLocale(), 3);

        return $this->render('hello/pages/home.html.twig', ['form' => $form, 'page' => $page, 'showLinks' => true, 'cv' => true, 'articles' => $articles]);
    }

    #[Route(path: ['fr' => '/fr/cv', 'en' => '/cv', 'de' => '/de/cv'], name: 'cv', defaults: ['ref' => 'cv'])]
    public function cv(Request $request, $ref): Response
    {
        $page = $this->commonController->getPage($request, $ref);

        return $this->render('hello/pages/cv.html.twig', ['page' => $page, 'showLinks' => true, 'cv' => true]);
    }

    #[Route(path: ['fr' => '/fr/mentions-legales', 'en' => '/legal-mentions', 'de' => '/de/impressum'], name: 'legal_mention', defaults: ['ref' => 'legal_mention'])]
    public function legalMention(Request $request, $ref)
    {
        return $this->commonController->legalMention($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/plan-du-site', 'en' => '/sitemap', 'de' => '/de/sitemap'], name: 'sitemap', defaults: ['ref' => 'sitemap'])]
    public function sitemap(Request $request, $ref)
    {
        return $this->commonController->sitemap($request, $ref);
    }

    #[Route(path: ['fr' => '/fr/sitemap.xml', 'en' => '/sitemap.xml', 'de' => '/de/sitemap.xml'], name: 'sitemap_xml')]
    public function sitemapXml(Request $request)
    {
        return $this->commonController->sitemapXml($request);
    }

    #[Route(path: ['fr' => '/fr/rss', 'en' => '/rss', 'de' => '/de/rss'], name: 'rss')]
    public function rss(Request $request)
    {
        return $this->commonController->rss($request);
    }

    #[Route(path: ['fr' => '/fr/contact', 'en' => '/contact', 'de' => '/de/kontakt'], name: 'contact', defaults: ['ref' => 'contact'])]
    public function contact(Request $request, $ref)
    {
        return $this->commonController->contact($request, $ref);
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
