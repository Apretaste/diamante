<?php

class Service
{
	/**
	 * Service starting point
	 *
	 * @param Request
	 * @param Response
	 */
	public function _main (Request $request, Response $response)
	{
		// do not let non-diamant users to pass
		$level = Level::getLevel($request->person->experience);
		if($level < Level::DIAMANTE) return $response->setTemplate('message.ejs');

		// for diamant users
		$this->_grupo($request, $response);
	}

	/**
	 * Meet other diamant users
	 *
	 * @param Request
	 * @param Response
	 */
	public function _grupo (Request $request, Response $response)
	{
		// do not let non-diamant users to pass
		$level = Level::getLevel($request->person->experience);
		if($level < Level::DIAMANTE) return $response->setTemplate('message.ejs');

		// send data to the view
		$response->setTemplate("group.ejs");
	}

	/**
	 * Participa en la rifa Diamante
	 *
	 * @param Request
	 * @param Response
	 */
	public function _rifa (Request $request, Response $response)
	{
		// do not let non-diamant users to pass
		$level = Level::getLevel($request->person->experience);
		if($level < Level::DIAMANTE) return $response->setTemplate('message.ejs');

		// send data to the view
		$response->setTemplate("raffle.ejs");
	}
}