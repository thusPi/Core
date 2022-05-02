thusPiAssign('users.currentUser', {
    getSetting: (key) => {
        return thusPi.data?.users?.currentUser?.settings[key] ?? undefined;
    },

    setSetting: (key, value) => {
        return new Promise((resolve, reject) => {
            thusPi.api.call('user-set-setting', {'key': key, 'value': value}).then((response) => {
                resolve(response);
            }).catch(() => {
                reject(response);
            })
        })
    }
})