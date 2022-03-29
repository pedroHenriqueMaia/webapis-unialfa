<?php

namespace App\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Adapter\Exception\InvalidQueryException;
use Laminas\Db\Sql\Sql;
use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProdutoHandler implements RequestHandlerInterface
{
    public function __construct(
        private Adapter $dbAdapter
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];

        $method = $request->getMethod();
        $idProd = $request->getAttribute("idProd");

        $sql = new Sql($this->dbAdapter);

        switch ($method) {
            case 'GET':
                $data = [];

                $select = $sql->select('produto');
                $select->columns(['id', 'categoria_id', 'nome', 'preco']);

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
                $insert->values(['nome' => $body->nome, 'categoria_id' => $body->categoria_id, 'preco' => $body->preco], $insert::VALUES_MERGE);
                $stmt = $sql->prepareStatementForSqlObject($insert);
                $stmt->execute();

                return new EmptyResponse(201);
            break;

            case 'PATCH':
                $body = json_decode($request->getBody()->getContents());

                $update= $sql->update('produto');
                $update->set(['nome' => $body->nome]);
                $update->set(['categoria_id' => $body->categoria_id]);
                $update->set(['preco' => $body->preco]);
                $update->where(['id' => $idProd]);

                $stmt = $sql->prepareStatementForSqlObject($update);
                $recordSet = $stmt->execute();

                return new EmptyResponse(204);
            break;

            case 'DELETE':
                $delete = $sql->delete('produto');
                $delete->where(['id' => $request->$idProd]);

                $stmt = $sql->prepareStatementForSqlObject($delete);
                $recordSet = $stmt->execute();

                return new EmptyResponse(204);
            break;
        }
    }
}