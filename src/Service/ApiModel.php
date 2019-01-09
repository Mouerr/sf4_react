<?php

namespace App\Service;

use App\Api\RepLogApiModel;
use App\Entity\RepLog;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ApiModel extends AbstractController
{
    private $translator;
    private $logger;
    private $serializer;

    //private $isDebug;
    public function __construct(LoggerInterface $logger, TranslatorInterface $translator, SerializerInterface $serializer)
    {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->serializer = $serializer;
    }

    /**
     * @param mixed $data Usually an object you want to serialize
     * @param int $statusCode
     * @return JsonResponse
     */
    public function createApiResponse($data, $statusCode = 200)
    {
        $json = $this->serializer->serialize($data, 'json');

        return new JsonResponse($json, $statusCode, [], true);
    }

    /**
     * Returns an associative array of validation errors
     *
     * {
     *     'firstName': 'This value is required',
     *     'subForm': {
     *         'someField': 'Invalid value'
     *     }
     * }
     *
     * @param FormInterface $form
     * @return array|string
     */
    public function getErrorsFromForm(FormInterface $form)
    {
        foreach ($form->getErrors() as $error) {
            // only supporting 1 error per field
            // and not supporting a "field" with errors, that has more
            // fields with errors below it
            return $error->getMessage();
        }

        $errors = array();
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childError = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childError;
                }
            }
        }

        return $errors;
    }

    /**
     * @return RepLogApiModel[]
     */
    public function findAllUsersRepLogModels()
    {
        $repLogs = $this->getDoctrine()->getRepository(RepLog::class)
            ->findBy(array('user' => $this->getUser()));

        $models = [];
        foreach ($repLogs as $repLog) {
            $models[] = $this->createRepLogApiModel($repLog);
        }

        return $models;
    }

    /**
     * Turns a RepLog into a RepLogApiModel for the API.
     *
     * This could be moved into a service if it needed to be
     * re-used elsewhere.
     *
     * @param RepLog $repLog
     * @return RepLogApiModel
     */
    public function createRepLogApiModel(RepLog $repLog)
    {
        $model = new RepLogApiModel();
        $model->id = $repLog->getId();
        $model->reps = $repLog->getReps();
        $model->itemLabel = $this->translator->trans($repLog->getItemLabel());
        $model->totalWeightLifted = $repLog->getTotalWeightLifted();

        $selfUrl = $this->generateUrl(
            'rep_log_get',
            ['id' => $repLog->getId()]
        );
        $model->addLink('_self', $selfUrl);

        return $model;
    }
}