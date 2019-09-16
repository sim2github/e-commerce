<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ProductType;
use App\Entity\Product;
use App\Entity\Image;
use App\Service\Slugger;
use Symfony\Component\Translation\TranslatorInterface;

class ProductController extends AbstractController
{
    public function index(Request $req, $page)
    {
        $form = $this->createFormBuilder()
            ->add('search', SearchType::class)
            ->getForm();

        $form->handleRequest($req);

        $maxResults = 10;
        $firstResult = $maxResults * ($page - 1);

        if ($form->isSubmitted() && $form->isValid()) {
            $query = $form->getData();

            $products = $this->getDoctrine()
                ->getRepository(Product::class)
                ->search($query['search'], $firstResult, $maxResults);
        } else {
            $products = $this->getDoctrine()
                ->getRepository(Product::class)
                ->getPaginated($firstResult, $maxResults);
        }

        $totalResults = count($products);
        $totalPages = 1;
        if ($totalResults > 0) {
            $totalPages = ceil($totalResults / $maxResults);
        }

        return $this->render('admin/all_products.html.twig', [
            'products' => $products,
            'form' => $form->createView(),
            'total_pages' => $totalPages,
            'current_page' => $page,
        ]);
    }

    public function editor(Request $req, $id, Slugger $slugger, TranslatorInterface $translator)
    {
        $product = new Product();
        $title = $translator->trans('product.new');

        if ($id) {
            $product = $this->getDoctrine()
                ->getRepository(Product::class)
                ->find($id);

            if (!$product) {
                throw $this->createNotFoundException(
                    $translator->trans('product.not_exist')
                );
            }

            $title = $translator->trans('product.edit');
        } else {
            $product->addImage(new Image());
        }

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($req);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($product->getImages() as $image) {
                if ($file = $image->getFile()) {
                    $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $filesize = filesize($file);
                    $image->setSize($filesize);
                    $image->setName($filename);
                    $file->move($this->getParameter('images_directory'), $filename);
                }
            }

            $slug = $slugger->slugify($product);
            $product->setSlug($slug);

            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('success', $translator->trans('product.added'));

            return $this->redirect($this->generateUrl('admin_product-editor', [
                'id' => $product->getId(),
            ]));
        }

        return $this->render('admin/product_editor.html.twig', [
            'form' => $form->createView(),
            'title' => $title,
        ]);
    }

    public function delete($id, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository(Product::class)->find($id);
        $product->setDeletedAt(new \Datetime());

        $em->persist($product);
        $em->flush();

        $this->addFlash('success', $translator->trans('product.deleted'));

        return $this->redirectToRoute('admin_index');
    }
}
