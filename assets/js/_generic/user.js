thusPiAssign('users.currentUser', {
    getSetting(key) {
        const value = thusPi.data?.users?.currentUser?.settings[key];

        // Return value as boolean if it is a valid boolean string
        if(value == 'true' || value == 'false') {
            return (value == 'true');
        }

        return value;
    },

    setSetting(key, value) {
        return new Promise(function(resolve, reject) {
            thusPi.api.call('user-set-setting', {'key': key, 'value': value}).then(function(response) {
                resolve(response);
            }).catch(function() {
                reject(response);
            })
        })
    }
})