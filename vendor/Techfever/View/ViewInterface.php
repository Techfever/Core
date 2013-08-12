<?php

namespace Techfever\View;

use Countable;
use IteratorAggregate;

interface ViewInterface extends Countable, IteratorAggregate, ElementInterface, ElementPrepareAwareInterface, ViewFactoryAwareInterface {
	const VALIDATE_ALL = 0x10;
	const VALUES_NORMALIZED = 0x11;
	const VALUES_RAW = 0x12;
	const VALUES_AS_ARRAY = 0x13;

	/**
	 * Add an element
	 *
	 * $flags could contain metadata such as the alias under which to register
	 * the element, order in which to prioritize it, etc.
	 *
	 * @param  array|\Traversable|ElementInterface $element Typically, only allow objects implementing ElementInterface;
	 *                                                                however, keeping it flexible to allow a factory-based View
	 *                                                                implementation as well
	 * @param  array $flags
	 * @return ViewInterface
	 */
	public function add($element, array $flags = array());

	/**
	 * Does the have an element by the given name?
	 *
	 * @param  string $element
	 * @return bool
	 */
	public function has($element);

	/**
	 * Retrieve a named element
	 *
	 * @param  string $element
	 * @return ElementInterface
	 */
	public function get($element);

	/**
	 * Remove a named element
	 *
	 * @param  string $element
	 * @return ViewInterface
	 */
	public function remove($element);

	/**
	 * Set/change the priority of an element
	 *
	 * @param string $element
	 * @param int $priority
	 * @return ViewInterface
	 */
	public function setPriority($element, $priority);

	/**
	 * Retrieve all attached elements
	 *
	 * Storage is an implementation detail of the concrete class.
	 *
	 * @return array|\Traversable
	 */
	public function getElements();
}
