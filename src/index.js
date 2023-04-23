import config from '../config/links';
import plugins from './plugins';

const allPatterns = new RegExp(config.patterns.map(p => p.pattern).join('|'));
config.patterns.forEach((p, i) => config.patterns[i].regexp = new RegExp(p.pattern));

async function getResponse(options, request) {
    if (options.plugin) {
        options = await plugins.run(options, request);
    }

    if (options.url) {
        return Response.redirect(options.url);
    }

    if (options.body) {
        const headers = [];

        if (options.type) {
            headers['Content-Type'] = options.type;
        }

        return new Response(options.body, {headers});
    }

    if (options[0] == '/') {
        options = new URL(request.url).protocol + options;
    }

    return Response.redirect(options);
}

export default {
    async fetch(request, env, ctx) {
        const path = new URL(request.url).pathname.replace(/^\//, '');

        if (config.static[path]) {
            let dest = config.static[path];
            return await getResponse(dest, request);
        }
        else if (allPatterns.test(path)) {
            const pattern = config.patterns.find(p => p.regexp.test(path));
            let target = pattern.target;

            if (typeof target == 'string') {
                target = path.replace(pattern.regexp, target);
            }

            return await getResponse(target, request);
        }

        return new Response(404, {status: 404});
    }
};
