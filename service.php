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
	 * Chat with other diamant users
	 *
	 * @param Request
	 * @param Response
	 */
	public function _chat(Request $request, Response $response)
	{
		// get the list of latests chats
		$chats = Database::query("
			SELECT 
				A.message, A.inserted, B.avatar, B.avatarColor, B.gender, B.username,
				IF(B.id = {$request->person->id}, 'right', 'left') AS position
			FROM __diamante_chat A
			JOIN person B ON A.person_id = B.id
			ORDER BY inserted 
			DESC LIMIT 50");

		// create content for the view
		$content = [
			"username" => $request->person->username,
			"gender" => $request->person->gender,
			"avatar" => $request->person->avatar,
			"avatarColor" => $request->person->avatarColor,
			'chats' => $chats
		];

		// send data to the view
		$response->setTemplate("chat.ejs", $content);
	}

	/**
	 * Post a new chat in the conversation
	 *
	 * @param Request
	 * @param Response
	 */
	public function _escribir(Request $request, Response $response)
	{
		// get the message
		$message = $request->input->data->message ?? '';
		$message = trim(Database::escape($message));

		// do not allow empty messages
		if(empty($message)) {
			return false;
		}

		// insert message in the database
		Database::query("
			INSERT INTO __diamante_chat (person_id, message) 
			VALUES ({$request->person->id}, '$message')");
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
			SELECT id, description, end_date
			FROM __diamante_raffle 
			WHERE winner_id IS NULL
			AND CURRENT_TIMESTAMP BETWEEN start_date AND end_date");

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

		// get current the raffle members
		$raffleParticipants = Database::query("
			SELECT B.id, B.experience
			FROM __diamante_raffle_participants A
			JOIN person B ON A.person_id = B.id
			WHERE raffle_id = {$raffle->id}");

		// for each participant ...
		$totalExp = 0; 
		$isEnrolled = false;
		foreach ($raffleParticipants as $participant) {
			// check if is enrolled
			if($participant->id == $request->person->id) $isEnrolled = true;

			// calculate total experience
			$totalExp += $participant->experience;
		}

		// calculate the chances to win
		$chances = empty($totalExp) ? 100 : number_format(($request->person->experience * 100) / $totalExp, 2);

		// create data for the view
		$context = [
			'isEnrolled' => $isEnrolled,
			'raffle' => $raffle,
			'participants' => count($raffleParticipants),
			'chances' => $chances,
			'winners' => $winners
		];

		// send data to the view
		$response->setCache();
		$response->setTemplate("raffle.ejs", $context);
	}

	/**
	 * Registrate en la rifa Diamante
	 *
	 * @param Request
	 * @param Response
	 */
	public function _entrar(Request $request, Response $response)
	{
		// get the raffle id
		$id = $request->input->data->id ?? false;

		// insert into the database
		if($id) {
			Database::query("
				INSERT IGNORE INTO __diamante_raffle_participants (raffle_id, person_id)
				VALUES ($id, {$request->person->id})");
		}

		// get back to the raffle
		return $this->_rifa($request, $response);
	}
}
