<?php
require_once("AuthorizationResult.php");
require_once("XMLException.php");

/**
 * Encapsulates request authorization via XML that must have routes configured as:
 * <routes>
 * 	<route url="{PAGE_TO_AUTHORIZE" access="ROLE_GUEST|ROLE_USER" ... />
 * 	...
 * </routes>
 */
class XMLAuthorization {
	const ROLE_USER = "ROLE_USER";
	const ROLE_GUEST = "ROLE_GUEST";

	private $loggedInFailureCallback;
	private $loggedOutFailureCallback;
	
	/**
	 * Creates an object
	 *
	 * @param string $loggedInFailureCallback
	 * @param string $loggedOutFailureCallback
	 */
	public function __construct($loggedInFailureCallback, $loggedOutFailureCallback) {
		$this->loggedInFailureCallback = $loggedInFailureCallback;
		$this->loggedOutFailureCallback = $loggedOutFailureCallback;
	}
    
    /**
     * Performs an authorization task.
     * 
     * @param SimpleXMLElement $xml
     * @param string $routeToAuthorize
     * @param boolean $isAuthenticated
     * @throws ApplicationException If route is misconfigured.
     * @return AuthorizationResult
     */
    public function authorize(SimpleXMLElement $xml, $routeToAuthorize, $isAuthenticated) {
        $status = 0;
        $callbackURI = "";
        
    	// check rights 
    	$tmp = (array) $xml->routes;
    	$tmp = $tmp["route"];
    	if(!is_array($tmp)) $tmp = array($tmp);
    	foreach($tmp as $info) {
    		$path = (string) $info['url'];
    		if($path != $routeToAuthorize) continue;
    		
    		// check for misconfiguration
    		if(empty($info['access'])) throw new XMLException("Access not set for route!");
    		$principal = (string) $info["access"];
    		if(!in_array($principal,array(self::ROLE_GUEST,self::ROLE_USER))) throw new XMLException("Unrecognized role: ".$principal);
    		
    		// now perform rights check
    		if($principal == self::ROLE_USER && !$isAuthenticated) {
    			// not allowed
                $status = AuthorizationResultStatus::UNAUTHORIZED;
                $callbackURI = $this->loggedOutFailureCallback;
    		} else {
    			// allowed
    			$status = AuthorizationResultStatus::OK;
    		}
    	}
    	if($status==0) {
    		$status = AuthorizationResultStatus::NOT_FOUND;
    		$callbackURI = ($isAuthenticated?$this->loggedInFailureCallback:$this->loggedOutFailureCallback);
    	}
        return new AuthorizationResult($status,$callbackURI);
    }
}