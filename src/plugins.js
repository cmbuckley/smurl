async function md5(str) {
    const encodedText = new TextEncoder().encode(str);
    const digest = await crypto.subtle.digest('MD5', encodedText);
    const hashArray = Array.from(new Uint8Array(digest));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

const plugins = {
    gravatar: async function () {
        const url = new URL(this.url);
        const md5Email = await md5(url.pathname.replace(/^\/grav\/(.+)$/, '$1').trim().toLowerCase());
        return `https://www.gravatar.com/avatar/${md5Email}?d=mm`;
    },

    qsa: async function (url) {
        const reqUrl = new URL(this.url);
        if (reqUrl.search) {
            url += (url.includes('?') ? '&' : '?') + reqUrl.search.substring(1);
        }

        return url;
    },
};

export default {
    run: async (options, request) => {
        const params = options.params || [];
        return await plugins[options.plugin].apply(request, Array.isArray(params) ? params : [params]);
    }
}
