<?php

declare(strict_types=1);

namespace jasonw4331\PoliteTeleports;

final class TeleportRequest{
	private bool $isCancelled = false;

	public function __construct(
		public readonly string $fromTarget,
		public readonly string $toTarget,
		public readonly string $requester
	){}

	public function cancel() : void{
		$this->isCancelled = true;
	}

	public function isCancelled() : bool{
		return $this->isCancelled;
	}
}
