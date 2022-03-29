<?php

namespace App\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\Sql;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CategoriaHandler implements  RequestHandlerInterface
{
    private $containerName;
    private $dbAdapter;

    public function __construct(
        string $containerName,
        Adapter $dbAdapter
    ) {
        $this->containerName = $containerName;
        $this->dbAdapter     = $dbAdapter;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];

        $method = $request->getMethod();
        $idCategoria = $request->getAttribute("idCategoria");

        $sql = new Sql($this->dbAdapter);

        switch ($method) {
            case 'GET':
                $data = [];

                $select = $sql->select('categoria');
                $select->columns(['id', 'nome']);

                $stmt = $sql->prepareStatementForSqlObject($select);
                $recordSet = $stmt->execute();

                while(($record = $recordSet->current()) !== false) {
                    $data[] = $record;

                    $recordSet->next();
                }

                return new JsonResponse($data);
            break;

            case 'POST':
                $body = json_decode($request->getBody()->getContents());

                $insert = $sql->insert('categoria');
                $insert->values(['nome' => $body->nome], $insert::VALUES_MERGE);
                $stmt = $sql->prepareStatementForSqlObject($insert);
                $stmt->execute();

                return new EmptyResponse(201);
            break;

            case 'PATCH':
                $body = json_decode($request->getBody()->getContents());

                $update= $sql->update('categoria');
                $update->set(['nome' => $body->nome]);
                $update->where(['id' => $idCategoria]);

                $stmt = $sql->prepareStatementForSqlObject($update);
                $recordSet = $stmt->execute();

                return new EmptyResponse(204);
            break;

            case 'DELETE':
                $delete = $sql->delete('categoria');
                $delete->where(['id' => $request->$idCategoria]);

                $stmt = $sql->prepareStatementForSqlObject($delete);
                $recordSet = $stmt->execute();

                return new EmptyResponse(204);
            break;
        }
    }
} 