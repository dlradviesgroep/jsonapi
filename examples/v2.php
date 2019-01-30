<?php

use alsvanzelf\jsonapi\Document;
use alsvanzelf\jsonapi\ResourceDocument;
use alsvanzelf\jsonapi\CollectionDocument;
use alsvanzelf\jsonapi\DataDocument;
use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\objects\ErrorObject;
use alsvanzelf\jsonapi\objects\ResourceObject;

ini_set('display_errors', 1);
error_reporting(-1);

require '../vendor/autoload.php';

$type  = 'human';
$id    = 42;
$key   = 'foo';
$value = 'bar';
$array = [
	'baf' => 'baz',
];
$exception = new \Exception('foo', 422);
$errorId   = uniqid();

echo '<h2>Resource</h2><pre>';

$resource = new ResourceDocument($type, $id);
$resource->add($key, $value);
$resource->addMeta('metaAtRoot', 'foo');
$resource->addMeta('metaAtJsonapi', 'bar', Document::LEVEL_JSONAPI);
$resource->addMeta('metaAtResource', 'baf', Document::LEVEL_RESOURCE);
$resource->addLink('linkAtRoot', 'https://foo.exampe.com/', $meta=['foo' => 'bar']);
$resource->addLink('linkAtResource', 'https://baf.exampe.com/', $meta=['foo' => 'bar'], Document::LEVEL_RESOURCE);
$resource2 = new ResourceObject($type, ($id/2));
$resource2->add($key, $value);
$resource->addRelationship('author', $resource2);
$resource->sendResponse();

echo '</pre><h2>Collection</h2><pre>';

$collection = new CollectionDocument($type);
$collection->add($type, ($id*2), $array);
$collection->addResource($resource);
$collection->sendResponse();

echo '</pre><h2>Resource with to-many relationships</h2><pre>';

$resource = new ResourceDocument($type, ($id/2));
$resource->addRelationship('relationFromCollection', $collection);
$resource->addRelationship('relationFromArray', $collection->resources);
$resource->sendResponse();

echo '</pre><h2>Empty data</h2><pre>';

$jsonapi = new DataDocument();
$jsonapi->setHttpStatusCode(201);
$jsonapi->sendResponse();

echo '</pre><h2>Errors</h2><pre>';

$jsonapi = ErrorsDocument::fromException($exception);
$error = new ErrorObject();
$error->setGeneric('Title is too generic', 1);
$error->setOccurence('The title you entered ("foo") is too generic', $errorId, 'https://error.exampe.com/?q='.$errorId);
$error->addLink('linkAtError', 'https://error.exampe.com/');
$error->setActionLink('https://inspiration.exampe.com/', $meta=['label' => 'Need inspiration?']);
$error->blameJsonPointer('/data/attributes/title');
$error->blameQueryParameter('title');
$error->blamePostData('title');
$error->addMeta($key, $value);
$jsonapi->addErrorObject($error);
$jsonapi->addLink('linkAtRoot', 'https://root.exampe.com/');
if ($jsonapi->httpStatusCode !== 200) {
	echo '<em>Send with http status code: '.$jsonapi->httpStatusCode.'</em>'.PHP_EOL.PHP_EOL;
}
$jsonapi->sendResponse();

echo '</pre>';
