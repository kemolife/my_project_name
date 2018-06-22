var Setting = {

    apiKey : 'AIzaSyA2ii4Jk-1gfp6btAenEw7guUV1n_iCPOI',
    clientId : '662189537461-cqoa48iru0oulsg6uajl276efm0l9gc4.apps.googleusercontent.com',
    gmb_api_version : 'https://mybusiness.googleapis.com/v4',
    scopes : 'https://www.googleapis.com/auth/plus.business.manage',

    initClient : function () {
        gapi.client.init({
            apiKey: this.apiKey,
            clientId: this.clientId,
            scope: this.scopes
        }).then(function () {
            // Listen for sign-in state changes.
            gapi.auth2.getAuthInstance().isSignedIn.listen(updateSigninStatus);

            // Handle the initial sign-in state.
            updateSigninStatus(gapi.auth2.getAuthInstance().isSignedIn.get());

            authorizeButton.onclick = handleAuthClick;
            signoutButton.onclick = handleSignoutClick;
            categoriesButton.onclick = handleCategoriesClick;
        });
    },

    handleClientLoad : function () {
        api.load('client:auth2', initClient);
    }
};