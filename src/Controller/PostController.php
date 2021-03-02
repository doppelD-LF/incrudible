<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


/**
 * @Route("/post", name="post.")
 */
class PostController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts
        ]);
    }
    /**
     * @Route("/create", name="create")
     */
    public function create(Request $request) {
        //create a new post with title
        $post = new Post();

        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);

        $form->getErrors();

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $file = $request->files->get('post') ['image'];

            //TODO: Still persist & submit if no image file is found
            if($file) {
                $filename = md5(uniqid()) .  '.' . $file->guessClientExtension();
                
                $file->move(
                    $this->getParameter('uploads_dir'), 
                    $filename
                
                );

                $post->setImage($filename);
                $em->persist($post);
                $em->flush();
            }

            
            

            return $this->redirect($this->generateUrl('post.index'));
            
        }

        return $this->render('post/create.html.twig', [
            'form' => $form->createView()
        ]);
        
        
        
    }

    /**
     * @Route("/show/{id}", name="show")
     */
    public function show(Post $post) {

        //dump($post); 

        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @Route("/delete/{id}", name="delete")
     */
    public function remove($id, PostRepository $postRepository) {
        $em = $this->getDoctrine()->getManager();

        $post = $postRepository->find($id);
        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Post was deleted!');

        return $this->redirect($this->generateUrl('post.index'));
    }
}
