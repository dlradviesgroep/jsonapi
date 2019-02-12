<?php

namespace alsvanzelf\jsonapi;

use alsvanzelf\jsonapi\Document;

class MetaDocument extends Document {
	/**
	 * human api
	 */
	
	/**
	 * wrapper for Document::addMeta() to the primary data of this document available via `add()`
	 * 
	 * @param string $key
	 * @param mixed  $value
	 * @param string $level one of the Document::LEVEL_* constants, optional, defaults to Document::LEVEL_ROOT
	 */
	public function add($key, $value, $level=Document::LEVEL_ROOT) {
		return parent::addMeta($key, $value, $level);
	}
	
	/**
	 * spec api
	 */
	
	/**
	 * DocumentInterface
	 */
	
	/**
	 * @inheritDoc
	 */
	public function toArray() {
		$array = parent::toArray();
		
		// force meta to be set, and be an object when converting to json
		if (isset($array['meta']) === false) {
			$array['meta'] = new \stdClass();
		}
		
		return $array;
	}
}