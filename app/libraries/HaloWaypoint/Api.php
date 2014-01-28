<?php

namespace HaloWaypoint;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use jyggen\Curl as MCurl;
use Whoops\Example\Exception;

class Api {
	private $url = "https://stats.svc.halowaypoint.com";
	private $auth = "https://settings.svc.halowaypoint.com/RegisterClientService.svc/spartantoken/wlid";
	private $presence = "https://presence.svc.halowaypoint.com";

	private $lang = "english";

	private $game = "h4";

	/**
	 * @return array
	 */
	public function getChallenges()
	{
		if (Cache::has('CurrentChallenges'))
		{
			// check it
			$response = Cache::get('CurrentChallenges');

			if (time() > strtotime($response->Challenges['0']->EndDate, true))
			{
				Cache::forget('CurrentChallenges');
				return $this->getChallenges();
			}
			return $response->Challenges;
		}
		else
		{
			$response = $this->grabUrl("challenges", "default", false);
			Cache::put('CurrentChallenges', $response, 60 * 24);
			return $response->Challenges;
		}
	}

	public function getPlaylists()
	{
		if (Cache::has('CurrentPlaylists'))
		{
			return Cache::get('CurrentPlaylists');
		}
		else
		{
			$response = $this->grabUrl("playlists", "presence", true);

			// we only care about playlists that are in matchmaking (id 3)
			// and active (isCurrent). So lets trash the rest.
			// For the ones, we do want. Lets remove the Map/Game list, we
			// don't use those portions and it accounts for a lot of space.
			foreach($response->Playlists as $key => $playlist)
			{
				if ($playlist->ModeId == 3 && $playlist->IsCurrent === true)
				{
					unset($response->Playlists[$key]->GameVariants);
					unset($response->Playlists[$key]->MapVariants);
				}
				else
				{
					unset($response->Playlists[$key]);
				}
			}

			Cache::put('CurrentPlaylists', $response->Playlists, 60 * 24);
			return $response->Playlists;
		}
	}

	/**
	 * @throws \Whoops\Example\Exception
	 * @return string
	 */
	public function getSpartanAuthKey()
	{
		if (Cache::has('SpartanAuthKey'))
		{
			return Cache::get('SpartanAuthKey')->SpartanToken;
		}
		else
		{
			$url = Config::get('secret.SpartanAuthUrl');
			$request = new MCurl\Request($url);

			$request->execute();

			if ($request->isSuccessful())
			{
				$response = json_decode($request->getResponse()->getContent());

				if (time() > intval($response->expiresIn))
				{
					return $this->getSpartanAuthKey();
				}
				else
				{
					// at this point, we have a WLID AuthenticationToken
					// we must use this against the RegisterService of 343
					// to get a SpartanToken, which lasts for an hour
					// which we can use against authenticated api calls
					$request = new MCurl\Request($this->auth);
					$request->setOption(CURLOPT_HTTPHEADER, [
							'Accept: application/json',
							'X-343-Authorization-WLID: ' . 'v1=' . $response->accessToken
						]);
					$request->setOption(CURLOPT_SSL_VERIFYPEER, false);
					$request->setOption(CURLOPT_SSL_VERIFYHOST, false);
					$request->execute();

					if ($request->isSuccessful())
					{
						$response = json_decode($request->getResponse()->getContent());
						Cache::put('SpartanAuthKey', $response, 60);
						return $response->SpartanToken;
					}

					throw new Exception('Authorization URL is down');
				}
			}
			else
			{
				throw new Exception('SpartanToken URL is down');
			}
		}
	}

	private function getUrl($endpoint, $type = "default")
	{
		switch ($type)
		{
			case "presence":
				return $this->presence . "/" . $this->lang . "/" . $this->game . "/" . $endpoint;

			default:
				return $this->url . "/" . $this->lang . "/" . $this->game . "/" . $endpoint;

		}
	}

	private function checkStatus($response)
	{
		if (isset($response->StatusCode))
		{
			return $response;
		}

		return false;
	}

	private function getHeaders($auth = false)
	{
		if ($auth)
		{
			$key = $this->getSpartanAuthKey();

			$header_array = [
				'Accept: application/json',
				'X-343-Authorization-Spartan: ' . $key
			];
		}
		else
		{
			$header_array = [
				'Accept: application/json'
			];
		}

		return $header_array;
	}

	private function grabUrl($endpoint, $type = "default", $auth = false, $execute = true)
	{
		$url = $this->getUrl($endpoint, $type);
		$headers = $this->getHeaders($auth);

		$request = new MCurl\Request($url);
		$request->setOption(CURLOPT_HTTPHEADER, $headers);
		$request->setOption(CURLOPT_SSL_VERIFYPEER, false);
		$request->setOption(CURLOPT_SSL_VERIFYHOST, false);

		if ($execute === false)
		{
			return [
				'headers' => [
					CURLOPT_HTTPHEADER => $headers
				],
				'url'   => $url
			];
		}
		else
		{
			$request->execute();

			if ($request->isSuccessful())
			{
				$response = $request->getResponse()->getContent();
				return $this->checkStatus(json_decode($response));
			}
			else
			{
				return false;
			}
		}
	}

}