<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

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
    public function sendContact(Request $request, MailerInterface $mailer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['email']) || !isset($data['message']) || !isset($data['name'])) {
            return new JsonResponse(['status' => 'error', 'message' => 'Missing required fields.'], 400);
        }

        try {
            $email = (new Email())
                ->from($data['email'])
                ->to('williamsmithe@icloud.com')
                ->subject('Portfolio Contact: ' . $data['name'])
                ->text($data['message']);

            $mailer->send($email);

            return new JsonResponse(['status' => 'success', 'message' => 'Message sent successfully!']);
        } catch (\Exception $e) {
            return new JsonResponse(['status' => 'error', 'message' => 'Could not send email: ' . $e->getMessage()], 500);
        }
    }
}
