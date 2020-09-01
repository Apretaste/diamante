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
	public function _main(Request $request, Response $response)
	{
		// do not let non-diamant users to pass
		$level = Level::getLevel($request->person->experience);
		if ($level < Level::DIAMANTE) {
			$response->setCache();
			return $response->setTemplate('message.ejs', [
				"header" => "¡Espérate!",
				"icon" => "pan_tool",
				"text" => "Este servicio está lleno de sorpresas y premios especiales, pero es solo para usuarios que son nivel Diamante o superior. Sigue usando la app y pronto podrás acceder.",
				"button" => ""
			]);
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
	public function _grupo(Request $request, Response $response)
	{
		// get the list of diamant users
		$people = Database::query("
			SELECT username, avatar, avatarColor, gender, experience, online
			FROM person 
			WHERE active = 1
			AND blocked = 0
			AND experience >= 1000
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
	public function _rifa(Request $request, Response $response)
	{
		// get the current raffle running
		$raffle = Database::queryFirst("
			SELECT description, end_date
			FROM __diamante_raffle 
			WHERE winner_id IS NULL
			AND CURRENT_TIMESTAMP BETWEEN start_date AND end_date
			LIMIT 1");

		// error if no raffle is open
		if (empty($raffle)) {
			$response->setCache();
			return $response->setTemplate('message.ejs', [
				"header" => "No hay rifas abiertas",
				"icon" => "sentiment_very_dissatisfied",
				"text" => "Lo sentimos, no hay ninguna rifa abierta ahora mismo. Pruebe nuevamente mañana.",
				"button" => ["href" => "DIAMANTE GRUPO", "caption" => "Ver grupo"]
			]);
		}

		// get the list of winners
		$winners = Database::query("
			SELECT B.username, B.avatar, B.avatarColor, B.gender, B.online, A.end_date
			FROM __diamante_raffle A
			JOIN person B
			ON A.winner_id = B.id
			WHERE A.winner_id IS NOT NULL
			ORDER BY A.end_date DESC
			LIMIT 12");

		// get the total experience for diamond users
		$totalExp = Database::queryCache("
			SELECT SUM(experience) AS total 
			FROM person 
			WHERE active = 1
			AND blocked = 0
			AND experience >= 1000")[0]->total;

		// calculate the chances to win
		$chances = number_format(($request->person->experience * 100) / $totalExp, 2);

		// create data for the view
		$context = [
			'raffle' => $raffle,
			'chances' => $chances,
			'winners' => $winners];

		// send data to the view
		$response->setCache();
		$response->setTemplate("raffle.ejs", $context);
	}
}