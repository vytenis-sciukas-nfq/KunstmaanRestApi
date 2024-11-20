<?php

namespace Kunstmaan\Rest\TranslationsBundle\Controller;

use DateTime;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Kunstmaan\Rest\TranslationsBundle\Model\Exception\TranslationException;
use Kunstmaan\Rest\TranslationsBundle\Service\TranslationService;
use Kunstmaan\TranslatorBundle\Entity\Translation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

class TranslationsController extends AbstractFOSRestController
{
    /** @var TranslationService */
    private $translationsService;

    public function __construct(TranslationService $translationsService)
    {
        $this->translationsService = $translationsService;
    }

    /**
     * @View(
     *     statusCode=200
     * )
     *
     * @Rest\QueryParam(name="locale", nullable=false, description="locale")
     * @Rest\Get("/public/translations")
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return array
     *
     *
     * @OA\Get(
     *     path="/api/public/translations",
     *     description="Get a list of all translations",
     *     operationId="getTranslations",
     *     tags={"translations"},
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="the locale of the languages you want",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/listTranslation")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ErrorModel"
     *         )
     *     )
     * )
     */
    public function getTranslationsAction(ParamFetcherInterface $paramFetcher)
    {
        $locale = $paramFetcher->get('locale');

        if (!$locale) {
            throw new NotFoundHttpException('locale is required');
        }

        $translations = $this->getDoctrine()->getRepository('KunstmaanTranslatorBundle:Translation')
            ->findAllNotDisabled($locale, null);

        return $translations;
    }

    /**
     * @View(
     *     statusCode=200
     * )
     *
     * @Rest\QueryParam(name="locale", nullable=false, description="locale")
     * @Rest\Get("/public/translations/{domain}/")
     *
     * @OA\Get(
     *     description="Get a list of all translations by domain only",
     *     operationId="getTranslationsByDomain",
     *     tags={"translations"},
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="the locale of the languages you want",
     *         required=true,
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         in="path",
     *         description="the domain of the languages you want",
     *         required=false,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/listTranslation")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ErrorModel"
     *         )
     *     )
     * )
     */
    public function getTranslationsByDomainAction(ParamFetcherInterface $paramFetcher, $domain)
    {
        $locale = $paramFetcher->get('locale');

        if (!$locale) {
            throw new NotFoundHttpException('locale is required');
        }

        $translations = $this->getDoctrine()->getRepository('KunstmaanTranslatorBundle:Translation')
            ->findAllNotDisabled($locale, $domain);

        return $translations;
    }

    /**
     * @View(
     *     statusCode=200
     * )
     *
     * @Rest\QueryParam(name="locale", nullable=false, description="locale")
     * @Rest\Get("/public/translations/{domain}/{keyword}")
     *
     * @param string                $keyword
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Translation
     *
     *
     * @OA\Get(
     *     path="/api/public/translations/{domain}/{keyword}",
     *     description="Get a translation",
     *     operationId="getTranslation",
     *     tags={"translations"},
     *     @OA\Parameter(
     *         name="locale",
     *         in="query",
     *         description="the locale of the languages you want",
     *         required=true,
     *     ),
     *     @OA\Parameter(
     *         name="keyword",
     *         in="path",
     *         description="the keyword of the translation you want",
     *         required=true,
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         in="path",
     *         description="the domain of the languages you want",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @OA\JsonContent(ref="#/components/schemas/singleTranslation")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ErrorModel"
     *         )
     *     )
     * )
     */
    public function getTranslationAction($domain, $keyword, ParamFetcherInterface $paramFetcher)
    {
        $locale = $paramFetcher->get('locale');

        if (!$locale) {
            throw new NotFoundHttpException('locale is required');
        }

        /** @var Translation $translation */
        $translation = $this->getDoctrine()
            ->getRepository(Translation::class)
            ->findOneBy(['locale' => $locale, 'keyword' => $keyword, 'domain' => $domain]);

        if ($translation && !$translation->isDisabled()) {
            return $translation;
        }

        throw new NotFoundHttpException();
    }

    /**
     * @View(
     *     statusCode=200
     * )
     *
     * @Rest\QueryParam(name="force", nullable=false, description="Force=true will overwrite existing translations, otherwise will be skipped")
     * @Rest\Post("/translations/{domain}")
     *
     * @param Request $request
     *
     * @return array
     *
     *
     * @OA\Post(
     *     path="/api/translations/{domain}",
     *     description="Create multiple translations",
     *     operationId="createTranslation",
     *     tags={"translations"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="The posted translations",
     *         @OA\JsonContent(ref="#/components/schemas/postTranslations"),
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         in="path",
     *         description="the domain of the languages you want",
     *         required=true,
     *     ),
     *     @OA\Parameter(
     *         name="force",
     *         in="query",
     *         description="Force=true will overwrite existing translations, otherwise will be skipped",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Returned when successfully created",
     *         @OA\JsonContent(ref="#/components/schemas/listTranslation")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Returned when no translations are provided",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ErrorModel"
     *         )
     *     )
     * )
     */
    public function postTranslationsAction(Request $request, ParamFetcherInterface $paramFetcher, $domain = 'messages')
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $force = $paramFetcher->get('force') === "true" ? true : false;

        $translationCreator = $this->translationsService;
        $json = $request->getContent();
        $translations = json_decode($json, true);

        foreach ($translations as $key => $translation) {
            $translations[$key]['domain'] = $domain;
        }

        $output = [];
        $translations = $translationCreator->createCollectionFromArray($translations);
        foreach ($translations as $translation) {
            $output[] = $translationCreator->createOrUpdateTranslation($translation, $force);
        }

        return $output;
    }

    /**
     * @View(
     *     statusCode=201
     * )
     *
     * @Rest\Put("/translations/deprecate/{domain}")
     *
     * @OA\Put(
     *     path="/api/translations/deprecate/{domain}",
     *     description="deprecate translations by keyword",
     *     operationId="deprecateTranslation",
     *     tags={"translations"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="The posted translations",
     *         @OA\JsonContent(ref="#/components/schemas/keywordCollection"),
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         in="path",
     *         description="the domain of the languages you want",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Returned when successfully deprecated"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ErrorModel"
     *         )
     *     )
     * )
     */
    public function deprecateTranslationsAction(Request $request, $domain = 'messages')
    {
        $translationCreator = $this->translationsService;

        $json = $request->getContent();
        $keywords = json_decode($json, true);
        if (isset($keywords['keyword'])) {
            $keywords = [$keywords];
        }

        foreach ($keywords as $keyword) {
            if (!array_key_exists('keyword', $keyword)) {
                throw new TranslationException(TranslationException::NOT_VALID);
            }
        }

        foreach ($keywords as $keyword) {
            $translationCreator->deprecateTranslations($keyword['keyword'], $domain);
        }
    }

    /**
     * @View(
     *     statusCode=201
     * )
     *
     * @Rest\Put("/translations/disable/{domain}")
     *
     * @OA\Put(
     *     path="/api/translations/disable/{domain}",
     *     description="disable translations by keyword",
     *     operationId="disableTranslation",
     *     tags={"translations"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="The posted translations",
     *         @OA\JsonContent(ref="#/components/schemas/disablingDate"),
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         in="path",
     *         description="the domain of the languages you want",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Returned when successfully disabled"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ErrorModel"
     *         )
     *     )
     * )
     */
    public function disableDeprecatedTranslationsAction(Request $request, $domain)
    {
        $translationCreator = $this->translationsService;

        $json = $request->getContent();
        $translationDeprecation = json_decode($json, true);

        if (!array_key_exists('date', $translationDeprecation)) {
            throw new TranslationException(TranslationException::NOT_VALID);
        }

        $translationCreator->disableDeprecatedTranslations(new DateTime($translationDeprecation['date']), $domain);
    }

    /**
     * @param Request $request
     *
     * @throws TranslationException
     *
     *
     * @Rest\Put("/translations/enable/{domain}")
     *
     * @OA\Put(
     *     path="/api/translations/enable/{domain}",
     *     description="re-enable translations by keyword",
     *     operationId="enableTranslation",
     *     tags={"translations"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="The posted translations",
     *         @OA\JsonContent(ref="#/components/schemas/keywordCollection"),
     *     ),
     *     @OA\Parameter(
     *         name="domain",
     *         in="path",
     *         description="the domain of the languages you want",
     *         required=true,
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Returned when successfully enabled"
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="unexpected error",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/ErrorModel"
     *         )
     *     )
     * )
     */
    public function enableDeprecatedTranslationsAction(Request $request, $domain = 'messages')
    {
        $translationCreator = $this->translationsService;

        $json = $request->getContent();
        $keywords = json_decode($json, true);

        foreach ($keywords as $keyword) {
            if (!array_key_exists('keyword', $keyword)) {
                throw new TranslationException(TranslationException::NOT_VALID);
            }
        }

        foreach ($keywords as $keyword) {
            $translationCreator->enableDeprecatedTranslations($keyword['keyword'], $domain);
        }
    }
}
