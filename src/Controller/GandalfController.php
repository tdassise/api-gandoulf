<?php

namespace App\Controller;

use App\Entity\Gandalf;
use App\Repository\GandalfRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

#[Route('/api')]
class GandalfController extends AbstractController
{
    /**
     * Cette méthode permet de récupérer l'ensemble des chats.
     *
     * @OA\Response(
     *     response=200,
     *     description="Retourne la liste des livres",
     *     @OA\JsonContent(
     *        type="array",
     *        @OA\Items(ref=@Model(type=Gandalf::class, groups={"getGandoulves"}))
     *     )
     * )
     * @OA\Parameter(
     *     name="page",
     *     in="query",
     *     description="La page que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     *
     * @OA\Parameter(
     *     name="limit",
     *     in="query",
     *     description="Le nombre d'éléments que l'on veut récupérer",
     *     @OA\Schema(type="int")
     * )
     * @OA\Tag(name="Gandoulves")
     */
    #[Route('/gandoulves', name: 'app_gandoulves', methods: ['GET'])]
    public function getAllGandoulves(Request $request, GandalfRepository $gandalfRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        // récupération de la limite décidée
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        // création du cache
        $idCache = "getAllGandoulves-" . $page . "-" . $limit;
        // récupération des chats
        $gandoulvesList = $cachePool->get($idCache, function (ItemInterface $item) use ($gandalfRepository, $page, $limit) {
            $item->tag("gandoulvesCache");
            return $gandalfRepository->findAllWithPagination($page, $limit);
        });
        $context = SerializationContext::create()->setGroups("getGandoulves");
        // sérialisation des chats
        $jsonGandoulves = $serializer->serialize($gandoulvesList, 'json', $context);

        return new JsonResponse([
            'gandoulves' => $jsonGandoulves,
            Response::HTTP_OK,
            [],
            true,
        ]);
    }

    #[Route('/gandoulves/{id}', name: 'app_gandoulves_detail', methods: ['GET'])]
    public function getGandoulfDetail(SerializerInterface $serializer, Gandalf $gandoulf): JsonResponse
    {
        $context = SerializationContext::create()->setGroups("getGandoulves");
        $jsonBook = $serializer->serialize($gandoulf, 'json', $context);
        return new JsonResponse($jsonBook, Response::HTTP_OK, [], true);
    }

    #[Route('/gandoulves/delete/{id}', name: 'app_gandoulves_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un chat !')]
    public function deleteGandoulf(Gandalf $gandoulf, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($gandoulf);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/gandoulves/add', name: 'app_gandoulves_create', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour ajouter un chat !')]
    public function createGandoulf(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $context = SerializationContext::create()->setGroups("getGandoulves");
        $gandoulf = $serializer->deserialize($request->getContent(), Gandalf::class, 'json');

        // On vérifie les erreurs
        $errors = $validator->validate($gandoulf);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json', $context), Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($gandoulf);
        $em->flush();

        $jsonGandoulf = $serializer->serialize($gandoulf, 'json', $context);

        return new JsonResponse($jsonGandoulf, Response::HTTP_CREATED, [], true);
    }

    #[Route('/gandoulves/edit/{id}', name: 'app_gandoulves_update', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour modifier un chat !')]
    public function updateGandoulf(Request $request, GandalfRepository $gandalfRepository, SerializerInterface $serializer, EntityManagerInterface $em,ValidatorInterface $validator, int $id): JsonResponse
    {
        $context = SerializationContext::create()->setGroups("getGandoulves");

        $gandoulf = $gandalfRepository->find($id);
        $data = $serializer->deserialize($request->getContent(), Gandalf::class, 'json');
        if ($data->getTitle()) {
            $gandoulf->setTitle($data->getTitle());
        }
        if ($data->getUrl()) {
            $gandoulf->setUrl($data->getUrl());
        }

        // On vérifie les erreurs
        $errors = $validator->validate($gandoulf);

        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json', $context), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($gandoulf);
        $em->flush();

        $jsonGandoulf = $serializer->serialize($gandoulf, 'json', $context);

        return new JsonResponse($jsonGandoulf, Response::HTTP_CREATED, [], true);
    }
}
