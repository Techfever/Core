<?php

namespace Techfever\View;

interface ElementAttributeRemovalInterface {
	/**
	 * Remove a single element attribute
	 *
	 * @param  string $key
	 * @return ElementAttributeRemovalInterface
	 */
	public function removeAttribute($key);

	/**
	 * Remove many attributes at once
	 *
	 * @param array $keys
	 * @return ElementAttributeRemovalInterface
	 */
	public function removeAttributes(array $keys);

	/**
	 * Remove all attributes at once
	 *
	 * @return ElementAttributeRemovalInterface
	 */
	public function clearAttributes();
}
