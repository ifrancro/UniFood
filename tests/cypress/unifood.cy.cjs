const login=(email,password='password')=>{cy.visit('/login');cy.get('#email').type(email);cy.get('#password').type(password);cy.get('form[action$="/login"] button[type=submit]').click();};
describe('UniFood flujos de acceso y experiencia',()=>{
 it('CP-01 rechaza credenciales incorrectas',()=>{login('estudiante@unifood.bo','incorrecta');cy.contains('Las credenciales proporcionadas').should('be.visible');cy.location('pathname').should('eq','/login');});
 it('CP-01 estudiante accede al catálogo',()=>{login('estudiante@unifood.bo');cy.location('pathname').should('eq','/catalogo');cy.contains('Café Americano').should('exist');});
 it('CP-02 estudiante no puede administrar',()=>{login('estudiante@unifood.bo');cy.request({url:'/admin/productos',failOnStatusCode:false}).its('status').should('eq',403);});
 it('CP-02 empleado no puede administrar',()=>{login('empleado@unifood.bo');cy.location('pathname').should('eq','/panel/pedidos');cy.request({url:'/admin/productos',failOnStatusCode:false}).its('status').should('eq',403);});
 it('CP-13 confirmación propia permite volver sin desactivar',()=>{login('admin@unifood.bo');cy.visit('/admin/productos');cy.get('form[data-confirm]').first().as('form');cy.get('@form').find('button').click();cy.get('[data-confirm-dialog]').should('be.visible');cy.get('.confirmation-actions [data-confirm-cancel]').click();cy.get('[data-confirm-dialog]').should('not.be.visible');cy.get('@form').should('exist');cy.screenshot('administracion-confirmacion-cancelada',{capture:'viewport'});});
 it('CP-16 catálogo móvil sin desbordamiento horizontal',()=>{cy.viewport(390,844);login('estudiante@unifood.bo');cy.get('[data-product-name]').should('have.length.greaterThan',0);cy.document().then(doc=>expect(doc.documentElement.scrollWidth).to.be.at.most(390));cy.get('[data-product-name]').last().should('have.css','opacity','1');cy.get('[data-product-name]').first().scrollIntoView({offset:{top:-100,left:0}});cy.screenshot('catalogo-movil',{capture:'viewport'});});
 it('CP-06 carrito vacío presenta estado claro',()=>{login('estudiante@unifood.bo');cy.visit('/carrito');cy.contains('Tu carrito está vacío').should('be.visible');});
 it('CP-18 confirma una vez y rechaza repetir un avance desde una pantalla antigua',()=>{
  login('estudiante@unifood.bo');
  cy.intercept('POST','/carrito/agregar').as('add');
  cy.get('form[data-add-form]').first().find('button[type=submit]').click();
  cy.wait('@add');
  cy.visit('/carrito');
  cy.get('form[data-confirm-order-form]').then(form=>{
   const payload={_token:form.find('[name="_token"]').val(),order_token:form.find('[name="order_token"]').val()};
   cy.wrap(form).find('button[type=submit]').click();
   cy.location('pathname').should('eq','/mis-pedidos');
   cy.get('.order-code').should('have.length',1).invoke('text').then(code=>{
    cy.request('POST','/pedidos',payload);
    cy.visit('/mis-pedidos');
    cy.get('.order-code').should('have.length',1).and('contain',code);
    cy.clearCookies();
    login('empleado@unifood.bo');
    cy.contains('.order-card',code).find('form').then(stateForm=>{
     const action=stateForm.attr('action');
     const statePayload={_token:stateForm.find('[name="_token"]').val(),estado_esperado:stateForm.find('[name="estado_esperado"]').val()};
     cy.wrap(stateForm).find('button[type=submit]').click();
     cy.request('POST',action,statePayload);
     cy.visit('/panel/pedidos');
     cy.contains('.order-card',code).find('[name="estado_esperado"]').should('have.value','en_preparacion');
     cy.screenshot('pedido-confirmacion-y-estado-protegidos',{capture:'viewport'});
    });
   });
  });
 });
});
