import {Builder,By,until} from 'selenium-webdriver';
import chrome from 'selenium-webdriver/chrome.js';
import fs from 'node:fs/promises';
const base=process.env.E2E_URL||'http://127.0.0.1:8011';
const out='evidencias_sqa/automatizacion';await fs.mkdir(out,{recursive:true});
const options=new chrome.Options().addArguments('--headless=new','--window-size=1280,900');
if(process.env.CHROME_BINARY) options.setChromeBinaryPath(process.env.CHROME_BINARY);
let builder=new Builder().forBrowser('chrome').setChromeOptions(options);
if(process.env.CHROMEDRIVER) builder=builder.setChromeService(new chrome.ServiceBuilder(process.env.CHROMEDRIVER));
const results=[];let driver;
async function check(name,fn){const start=Date.now();try{await fn();results.push({name,status:'passed',ms:Date.now()-start});}catch(e){results.push({name,status:'failed',message:e.message,ms:Date.now()-start});if(driver) await fs.writeFile(`${out}/selenium-fallo-${results.length}.png`,await driver.takeScreenshot(),'base64');}}
async function login(email){await driver.manage().deleteAllCookies();await driver.get(base+'/login');await driver.findElement(By.id('email')).sendKeys(email);await driver.findElement(By.id('password')).sendKeys('password');await driver.findElement(By.css('form[action$="/login"] button[type=submit]')).click();await driver.wait(async()=>!(await driver.getCurrentUrl()).endsWith('/login'),10000);}
try{
 driver=await builder.build();await driver.manage().setTimeouts({implicit:1000,pageLoad:20000});
 await check('CP-01 login estudiante con navegador real',async()=>{await login('estudiante@unifood.bo');if(!(await driver.getCurrentUrl()).endsWith('/catalogo'))throw Error('Destino incorrecto');});
 await check('CP-06 carrito vacío',async()=>{await driver.get(base+'/carrito');await driver.wait(until.elementLocated(By.xpath('//*[contains(text(),"Tu carrito está vacío")]')),10000);});
 await check('CP-13 cancelar confirmación administrativa',async()=>{await login('admin@unifood.bo');await driver.get(base+'/admin/productos');await driver.findElement(By.css('form[data-confirm] button')).click();const dialog=await driver.findElement(By.css('[data-confirm-dialog]'));await driver.wait(until.elementIsVisible(dialog),5000);await fs.writeFile(out+'/selenium-confirmacion.png',await driver.takeScreenshot(),'base64');await driver.findElement(By.css('.confirmation-actions [data-confirm-cancel]')).click();if(await dialog.isDisplayed())throw Error('El diálogo sigue abierto');});
 await check('CP-16 catálogo móvil',async()=>{await driver.manage().window().setRect({width:390,height:844});await login('estudiante@unifood.bo');const dimensions=await driver.executeScript('return [document.documentElement.scrollWidth,window.innerWidth]');if(dimensions[0]>dimensions[1])throw Error('Desbordamiento horizontal');await fs.writeFile(out+'/selenium-movil.png',await driver.takeScreenshot(),'base64');});
}catch(e){results.push({name:'Inicio del navegador',status:'blocked',message:e.message});}
finally{if(driver)await driver.quit();await fs.writeFile(out+'/selenium-resultados.json',JSON.stringify({date:new Date().toISOString(),base,results},null,2));console.log(JSON.stringify(results,null,2));if(results.some(r=>r.status!=='passed'))process.exitCode=1;}
