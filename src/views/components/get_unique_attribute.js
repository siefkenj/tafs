// returns the list of unique values from the provided attribute an object in an array of objects
// example input: [{a:1},{a:2},{a:5},{a:2},{a:5}]
// example output: [1,2,5]
function get_unique_attribute(data, attribute) {
    //turn list of object into list of attirbutes within the object
    //then store list into a set which removes duplicates
    return [...new Set(data.map(el => el[attribute]))];
}
export default get_unique_attribute;
