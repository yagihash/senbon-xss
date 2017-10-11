const puppeteer = require("puppeteer");

let key = process.env.KEY;
let flag = process.env.FLAG;
let url = process.env.URL;

(async () => {
  const opt = {
    headless: true,
  };
  const browser = await puppeteer.launch(opt);
  const page = await browser.newPage();
  await page.goto("http://" + key + ".knock.xss.moe/AAAAAAA", {waitUntil: 'networkidle'});
  await page.setCookie({
    name: "flag",
    value: flag,
  });
  await page.goto(url, {waitUntil: 'networkidle'});
  await page.click("#target");
  await page.waitFor(3000);
  await browser.close();
})();
