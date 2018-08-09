/**
 * Function takes a json object and returns a url to get the specified
 * information from the API.
 */

function generate_query_string(parameters) {
    let arr = Object.keys(parameters)
        .filter(key => parameters[key] != "" && parameters[key] != null )
        .map(key => "" + key + "=" + parameters[key]);
    return arr.join("&");
}
export default generate_query_string;
