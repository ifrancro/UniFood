const {defineConfig}=require('cypress');
const path=require('node:path');
const output=process.env.SQA_OUTPUT_DIR||'evidencias_sqa/automatizacion';
module.exports=defineConfig({video:false,screenshotOnRunFailure:true,screenshotsFolder:path.join(output,'cypress-screenshots'),e2e:{baseUrl:process.env.E2E_URL||'http://127.0.0.1:8011',supportFile:false,specPattern:'tests/cypress/**/*.cy.cjs'},reporter:'junit',reporterOptions:{mochaFile:path.join(output,'cypress-[hash].xml')}});
