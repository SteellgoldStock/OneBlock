<?php

namespace steellgold\oneblock\instances;

class Rank {

	/**
	 * @param string $name
	 * @param string|array $permissions
	 */
	public function __construct(
		public string       $name,
		public string|array $permissions,
	) {

	}

	public function getName(): string {
		return $this->name;
	}

	public function getPermissions(): string|array {
		return $this->permissions;
	}

	public function hasPermission(string $permission): bool {
		if ($this->permissions == "*") {
			return true;
		}

		return in_array($permission, $this->permissions);
	}
}