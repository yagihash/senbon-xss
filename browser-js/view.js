const Chromy = require('chromy')

// not headless
// let chromy = new Chromy({visible:true})
let timeout = 1000;
let chromy = new Chromy({
    waitTimeout: timeout,
    gotoTimeout: timeout,
    evaluateTImeout: timeout
});

let key = process.env.KEY
let flag = process.env.FLAG
let url = process.env.URL

try {
    chromy.chain()
        .goto("http://" + key + ".knock.xss.moe/AAAAAA")
        .setCookie({name: "flag", value: flag})
        .goto(url)
        .waitLoadEvent()
        .evaluate(() => {
            return document.querySelectorAll('*').length;
        })
        .result((r) => console.log(r))
        .end()
        .then(() => chromy.close())
} catch (e) {
    Chromy.cleanup()
    process.exit(1)
}

process.on('SIGINT', async () => {
    await Chromy.cleanup()
    process.exit(1)
})

process.on('unhandledRejection', async () => {
    await Chromy.cleanup()
    process.exit(1)
})