thusPiAssign('api', {
    call(url, data) {
        url = `${thusPi.data.webroot}/api/${url}/`;
        let headers = {};
        let tokenId = localStorage.getItem('thusPi_tokenId');
        let token = localStorage.getItem('thusPi_token');

        if(token !== null && tokenId !== null) {
            headers['x-token-id'] = tokenId;
            headers['x-token']    = token;
        }
        
        return new Promise(function(resolve, reject) {
            let callStart;

            $.ajax({
                method: 'POST',
                data: data,
                url: url,
                headers: headers,
                async: true,
                beforeSend: function() {
                    callStart = Date.now()
                },
                success: function(response) {
                    if(typeof response.success == 'undefined' || response.success !== true) {
                        reject(response);
                        return false;
                    }
                    
                    response.took = Date.now() - callStart;

                    resolve(response);
                    return true;
                },
                error: function(response) {
                    console.error('An error occured while making a POST request to', url);
                    console.error(response);

                    if(response.responseJSON) {
                        if(response.responseJSON?.info == 'error_authorization') {
                            thusPi.page.load(`login/main?redirect=home/main`, true);
                        }
                        reject(response.responseJSON);
                    }

                    return false;
                }
            })
        })
    }
})