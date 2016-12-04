<?php
namespace OAuth2;

/**
 * Encapsulates a refresh token request according to RFC6749
 */
class RefreshTokenRequest {
	protected $endpointURL;
	protected $refreshToken;
	protected $scope;
	
	/**
	 * Location URL of refresh token endpoint @ Oauth2 Server
	 *
	 * @param string $endpointURL
	 */
	public function __construct($endpointURL) {
		$this->endpointURL = $endpointURL;
	}

	/**
	 * Sets refresh token originally issued to the client.
	 *
	 * @param string $refreshToken
	 */
	public function setRefreshToken($refreshToken) {
		$this->refreshToken = $refreshToken;
	}

	/**
	 * Sets scope of token issued by the authorization server.
	 *
	 * @param string $scope
	 */
	public function setScope($scope) {
		$this->scope = $scope;
	}
	
	/**
	 * Executes request and wraps response.
	 * 
	 * @param RequestExecutor $executor Performs request execution.
	 * @param ResponseWrapper $wrapper Performs wrapping of response. 
	 * @throws ClientException
	 */
	public function execute(RequestExecutor $executor, ResponseWrapper $wrapper) {
		if(!$this->refreshToken) throw new ClientException("Refresh token is required for refresh token requests!");
		$parameters = array();
		$parameters["grant_type"] = "refresh_token";
		$parameters["refreshToken"] = $this->refreshToken;
		if($this->scope) {
			$parameters["scope"] = $this->scope;
		}
		$wrapper->wrap($executor->execute($this->endpointURL, $parameters));
	}
}