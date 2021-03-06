export default class ReqwestApiClient {
    constructor(reqwest) {
        this._reqwest = reqwest;
    }

    getPostalCodeCities(postalCode, callback) {
        this._createRequest(callback, {
            url: '/api/postal-code/'+postalCode,
            type: 'json'
        });
    }

    getCommitteeTimelineFeed(committeeUuid, committeeSlug, offset, callback) {
        this._createRequest(callback, {
            url: '/comites/'+committeeUuid+'/'+committeeSlug+'/timeline?offset='+offset,
            type: 'html'
        });
    }

    getReferents(callback) {
        this._createRequest(callback, {
            url: '/api/referents',
            type: 'json'
        });
    }

    toggleCommitteeMembership(committeeUuid, committeeSlug, action, token, callback) {
        let request = this._reqwest({
            url: '/comites/'+committeeUuid+'/'+committeeSlug+'/'+action,
            type: 'html',
            method: 'post',
            data: {
                'token': token
            }
        });

        request.then((response) => {
            callback(JSON.parse(response));
        });

        request.fail(() => {
            callback(null);
        });
    }

    _createRequest(callback, parameters) {
        let request = this._reqwest(parameters);

        request.then((response) => {
            callback(response);
        });

        request.fail(() => {
            callback(null);
        });
    }
}
