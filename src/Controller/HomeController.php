<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $projects = [
            [
                'title' => "XLIDE:\nExcel VBA for VS Code",
                'description' => 'A modern VS Code workspace for Excel VBA projects featuring direct read/write, static analysis, and agentic AI integrations.',
                'tags' => ['TypeScript', 'Python', 'VBA', 'VS Code Extension'],
                'link' => 'https://github.com/WilliamSmithEdward/xlide_vscode'
            ],
            [
                'title' => 'pyOpenVBA',
                'description' => 'A pure-Python reader and writer for VBA macros in Office files, enabling deterministic round-trips and server-side automation without Office installed.',
                'tags' => ['Python', 'VBA', 'Office Automation', 'Open Source'],
                'link' => 'https://github.com/WilliamSmithEdward/pyOpenVBA'
            ],
            [
                'title' => 'Epiphany',
                'description' => 'A self-hostable, in-memory multidimensional OLAP server with a clean REST API and a React + TypeScript web UI built in Rust.',
                'tags' => ['Rust', 'TypeScript', 'React', 'OLAP'],
                'link' => 'https://github.com/WilliamSmithEdward/Epiphany'
            ]
        ];

        return $this->render('home/index.html.twig', [
            'projects' => $projects
        ]);
    }

    #[Route('/contact/send', name: 'contact_send', methods: ['POST'])]
    public function sendContact(Request $request, MailerInterface $mailer, HttpClientInterface $httpClient, LoggerInterface $logger): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email']) || !isset($data['message']) || !isset($data['name']) || !isset($data['recaptcha_response'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing required fields.'], 400);
        }

        if (empty(trim($data['name'])) || empty(trim($data['email'])) || empty(trim($data['message']))) {
            return new JsonResponse(['status' => 'error', 'message' => 'Fields cannot be empty.'], 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['status' => 'error', 'message' => 'Invalid email format.'], 400);
        }

        // Verify reCAPTCHA
        try {
            $response = $httpClient->request('POST', 'https://www.google.com/recaptcha/api/siteverify', [
                'body' => [
                    'secret'   => $_ENV['RECAPTCHA_SECRET_KEY'],
                    'response' => $data['recaptcha_response'],
                    'remoteip' => $request->getClientIp(),
                ],
            ]);

            $result = $response->toArray();
            if (!$result['success']) {
                return new JsonResponse(['status' => 'error', 'message' => 'CAPTCHA verification failed. Please try again.'], 400);
            }
        } catch (\Exception $e) {
            $logger->error('Contact CAPTCHA verification failed.', ['exception' => $e]);

            return new JsonResponse(['status' => 'error', 'message' => 'Could not verify CAPTCHA. Please try again later.'], 500);
        }

        try {
            $email = (new Email())
                ->from('noreply@williamsmithe.com')
                ->replyTo($data['email'])
                ->to('williamsmithe@icloud.com')
                ->subject('Portfolio Contact: ' . $data['name'])
                ->html("Name: {$data['name']}<br />Email: {$data['email']}<br /><br />Message: {$data['message']}")
                ->text("Name: {$data['name']}\nEmail: {$data['email']}\n\nMessage: {$data['message']}");

            $mailer->send($email);

            return new JsonResponse(['status' => 'success', 'message' => 'Message sent successfully!']);
        } catch (\Exception $e) {
            $logger->error('Contact email delivery failed.', ['exception' => $e]);

            return new JsonResponse(['status' => 'error', 'message' => 'Could not send your message. Please try again later.'], 500);
        }
    }
}
