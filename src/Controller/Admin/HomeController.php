<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/{_locale}', name: 'admin_', host: '%admin_host%', priority : -1, requirements: ['_locale' => 'en|fr|de'])]
class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index()
    {
        return $this->render('admin/home/index.html.twig');
    }
    #[Route('/upload', name: 'upload')]
    public function upload(Request $request)
    {
        $file = $request->files->get('upload');
        $name = mt_rand(0, 9999) . time() . '.' . $file->guessExtension();
        $file->move($this->get('kernel')->getRootDir() . '/../web/uploads/ckeditor', $name);
        $html = "<br/><br/><p style='font: normal normal normal 12px Arial,Helvetica,Tahoma,Verdana,Sans-Serif;'>";
        $html .= 'Copy & Paste this link in tab picture informations.</p>';
        $html .= "<p style='font: normal normal normal 12px Arial,Helvetica,Tahoma; color: #69b10b;'>";
        $html .= '/uploads/ckeditor/' . $name . '</p>';
        return new Response($html);
    }
    #[Route('/browser', name: 'browser')]
    public function browser()
    {
        $dirname = $this->get('kernel')->getRootDir() . '/../web/uploads/ckeditor';
        $dir     = opendir($dirname);
        $style   = "style='font: normal normal normal 12px Arial,Helvetica,Tahoma,Verdana,Sans-Serif;'";
        $results = '<div ' . $style . '><p>Copy & Paste for use it</p><ul>';
        while ($file = readdir($dir)) {
            if ($file != '.' && $file != '..' && !is_dir($dirname . $file)) {
                $results .= '<li style="display:inline-block; padding: 10px; margin-right: 10px; margin-bottom: 10px;';
                $results .= 'min-width: 200px; height: 150px; background: #bfbfbf;">';
                $results .= '<p style="text-align: center;"><img style="width: 150px; max-height: 100px;" ';
                $results .= 'src="/uploads/ckeditor/' . $file . '"/></p>';
                $results .= '<p style="color: black;">/uploads/ckeditor/' . $file . '</p></li>';
            }
        }
        closedir($dir);
        $results .= '</ul></div>';
        return new Response($results);
    }
    #[Route('/imagine/flush', name: 'imagine_flush')]
    public function imagineFlush()
    {
    }
    #[Route('/imagine/generate', name: 'imagine_generate')]
    public function imagineGenerate()
    {
    }
}
