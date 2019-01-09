<?php

namespace App\Controller;

use App\Api\ApiRoute;
use App\Entity\RepLog;
use App\Form\Type\RepLogType;
use App\Service\ApiModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @IsGranted("ROLE_USER")
 * @ApiRoute()
 */
class RepLogController extends AbstractController
{
    private $apiModel;

    public function __construct(ApiModel $apiModel)
    {
        $this->apiModel = $apiModel;
    }

    /**
     * @Route("/reps", name="rep_log_list", methods={"GET"}, options={"expose" = true})
     */
    public function getRepLogsAction()
    {

        $models = $this->apiModel->findAllUsersRepLogModels();

        return $this->apiModel->createApiResponse([
            'items' => $models
        ]);
    }

    /**
     * @Route("/reps/{id}", methods={"GET"}, name="rep_log_get")
     */
    public function getRepLogAction(RepLog $repLog)
    {
        $apiModel = $this->apiModel->createRepLogApiModel($repLog);

        return $this->apiModel->createApiResponse($apiModel);
    }

    /**
     * @Route("/reps/{id}", name="rep_log_delete", methods={"DELETE"})
     */
    public function deleteRepLogAction(RepLog $repLog)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $em = $this->getDoctrine()->getManager();
        $em->remove($repLog);
        $em->flush();

        return new Response(null, 204);
    }

    /**
     * @Route("/reps", name="rep_log_new", methods={"POST"}, options={"expose" = true})
     */
    public function newRepLogAction(Request $request)
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new BadRequestHttpException('Invalid JSON');
        }

        $form = $this->createForm(RepLogType::class, null, [
            'csrf_protection' => false,
        ]);
        $form->submit($data);
        if (!$form->isValid()) {
            $errors = $this->apiModel->getErrorsFromForm($form);

            return $this->apiModel->createApiResponse([
                'errors' => $errors
            ], 400);
        }

        /** @var RepLog $repLog */
        $repLog = $form->getData();
        $repLog->setUser($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($repLog);
        $em->flush();

        $apiModel = $this->apiModel->createRepLogApiModel($repLog);

        $response = $this->apiModel->createApiResponse($apiModel);
        // setting the Location header... it's a best-practice
        $response->headers->set(
            'Location',
            $this->generateUrl('rep_log_get', ['id' => $repLog->getId()])
        );

        return $response;
    }
}
