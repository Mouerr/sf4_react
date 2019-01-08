function fetchJson(url, options) {
    let headers = {'Content-Type': 'application/json'};
    if (options && options.headers) {
        headers = {...options.headers, headers};
        delete options.headers;
    }

    return fetch(url, Object.assign({
        credentials: 'same-origin',
        headers: headers,
    }, options))
        .then(checkStatus)
        .then(response => {
            // decode JSON, but avoid problems with empty responses
            return response.text()
                .then(text => text ? JSON.parse(text) : '')
        });
}

function checkStatus(response) {
    if (response.status >= 200 && response.status < 400) {
        return response;
    }

    const error = new Error(response.statusText);
    error.response = response;

    throw error
}

/**
 * Returns a promise where the data is the rep log collection
 *
 * @return {Promise<Response>}
 */
export function getRepLogs() {
    return fetchJson('/reps')
        .then(data => data.items);
}

export function deleteRepLog(id) {
    return fetchJson(`/reps/${id}`, {
        method: 'DELETE'
    });
}

export function createRepLog(repLog) {
    return fetchJson('/reps', {
        method: 'POST',
        body: JSON.stringify(repLog)
    });
}
