<?php

declare(strict_types=1);

namespace jasonwynn10\PoliteTeleports;

final class TeleportRequest{
	private bool $isCancelled = false;

	public function __construct(
		private string $fromTarget,
		private string $toTarget,
		private string $requester
	){
	}

	public function getFromTarget() : string{
		return $this->fromTarget;
	}

	public function getToTarget() : string{
		return $this->toTarget;
	}

	public function getRequester() : string{
		return $this->requester;
	}

	public function cancel() : void{
		$this->isCancelled = true;
	}

	public function isCancelled() : bool{
		return $this->isCancelled;
	}
}
