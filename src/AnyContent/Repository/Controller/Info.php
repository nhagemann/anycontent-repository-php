<?php

namespace AnyContent\Repository\Controller;


use AnyContent\Repository\Service;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Info
{
    static function defaultAction(Request $request, Service $service, $repositoryName)
    {
        $repository = $service->getRepository($repositoryName);

        if ($repository) {
            $response = new JsonResponse();

            $etag = md5($repository->getLastModifiedDate());

            $response->setEtag($etag);

            if ($response->isNotModified($request)) {
                $response->send();
                return $response;
            }


            $result = [];
            $result['content'] = [];

            foreach ($repository->getContentTypeDefinitions() as $definition) {

                $result['content'][$definition->getName()]['title'] = $definition->getTitle() ? $definition->getTitle() : $definition->getName();
                $result['content'][$definition->getName()]['lastchange_content'] = $repository->getLastModifiedDate($definition->getName());
                $result['content'][$definition->getName()]['lastchange_cmdl'] = 0; // TODO
            }


            $response->setContent(json_encode($result, JSON_PRETTY_PRINT));
            $response->setEtag($etag);
            $response->setPublic();

            return $response;
        }


    }
}
