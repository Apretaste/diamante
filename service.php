<?php

use Apretaste\Level;
use Apretaste\Request;
use Apretaste\Response;
use Framework\Database;

class Service
{
	/**
	 * Service starting point
	 *
	 * @param Request
	 * @param Response
	 */
	public function _main(Request $request, Response &$response)
	{
		// do not let non-diamant users to pass
		$level = Level::getLevel($request->person->experience);
		if ($level < Level::DIAMANTE) {
			return $response->setTemplate('message.ejs');
		}

		// for diamant users
		$this->_grupo($request, $response);
	}

	/**
	 * Meet other diamant users
	 *
	 * @param Request
	 * @param Response
	 */
	public function _grupo(Request $request, Response &$response)
	{
		// do not let non-diamant users to pass
		$level = Level::getLevel($request->person->experience);
		if ($level < Level::DIAMANTE) {
			return $response->setTemplate('message.ejs');
		}

		// get the list of diamant users
		$people = Database::query("
			SELECT username, avatar, avatarColor, gender, experience
			FROM person 
			WHERE experience >= 1000
			ORDER BY experience DESC
			LIMIT 20");

		// send data to the view
		$response->setCache();
		$response->setTemplate("group.ejs", ['people' => $people]);
	}

	/**
	 * Participa en la rifa Diamante
	 *
	 * @param Request
	 * @param Response
	 */
	public function _rifa(Request $request, Response &$response)
	{
		// do not let non-diamant users to pass
		$level = Level::getLevel($request->person->experience);
		if ($level < Level::DIAMANTE) {
			return $response->setTemplate('message.ejs');
		}

		// send data to the view
		$response->setTemplate("raffle.ejs");
	}
}
