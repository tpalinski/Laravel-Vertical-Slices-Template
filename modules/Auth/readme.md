# Auth
Module used for handling login, registration, oauth token validation and endpoint security

## Version 0.9.0
Base working version providing login functionality and full client oauth flow

### Full login flow
1. User sends post request with password, login and clientId to /auth/login (clientId is the internal id of supported application - see config/auth.php)
2. Server responds with `authTicket` - a string containing a token to be exchanged during OAuth flow after.
3. Application then sends a get request to `/auth/authorize` with authTicket and other params (see api documentation)
4. Server responds with authorization code
5. Client sends a post request to `/auth/token` with the authorization code and other parameters (see api documentation) to be exchanged for access and refresh tokens.

