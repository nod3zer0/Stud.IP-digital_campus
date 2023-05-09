export default function extractCallback(cmd, payload, root = window) {
    var command = cmd,
        last_chunk = null,
        callback = root,
        previous = null;

    // Try to decode URI component in case it is encoded
    try {
        command = window.decodeURIComponent(command);
    } catch (ignore) {
        // No action necessary
    }

    // Try to parse value as JSON (value might be {func: 'foo', payload: {}})
    try {
        command = JSON.parse(command);
    } catch (e) {
        command = { func: command };
    }

    // Check for invalid call
    if (command.func === undefined) {
        throw 'Dialog: Invalid value for X-Dialog-Execute';
    }

    // Populate payload if not set
    if (command.payload === undefined) {
        command.payload = payload;
    }

    // Find callback
    command.func.trim().split(/\./).forEach(chunk => {
        // Check if last chunk was unfinished
        if (last_chunk !== null) {
            chunk = last_chunk + '.' + chunk;
            last_chunk = null;
        }

        // Check for not finished/closed chunk
        if (chunk.match(/\([^)]*$/)) {
            last_chunk = chunk;
            return;
        }

        previous = callback;

        var match = chunk.match(/\((.*)\);?$/),
            parameters = null;

        if (match !== null) {
            chunk = chunk.replace(match[0], '');
            try {
                parameters = JSON.parse('[' + match[1].replace(/'/g, '"') + ']');
            } catch (e) {
                console.log('error parsing json', match);
            }
        }

        if (callback[chunk] === undefined) {
            throw 'Error: Undefined callback ' + cmd;
        }

        if (typeof callback[chunk] === 'function' && parameters !== null) {
            callback = callback[chunk].apply(callback, parameters);
        } else {
            callback = callback[chunk];
        }
    });

    // Check callback
    if (typeof callback !== 'function') {
        return function() {
            return callback;
        };
    }

    return function(p) {
        return callback.apply(previous, [p || command.payload]);
    };
}
