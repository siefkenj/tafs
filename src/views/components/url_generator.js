/**
 * Function takes a json object and returns a url to get the specified
 * information from the API.
 */

function generate_get_url(parameters) {
    let query = "";
    for (let key in parameters) {
        if (parameters[key] != "") {
            if (query == "") {
                query = key + "=" + parameters[key];
            } else {
                query += "&" + key + "=" + parameters[key];
            }
        }
    }
    return `get_info.php?${query}`;
}
export default generate_get_url;
