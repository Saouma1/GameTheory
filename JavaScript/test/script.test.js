const chai = require('chai');
const expect = chai.expect;
const sinon = require('sinon');
const jsdom = require('jsdom');
const { JSDOM } = jsdom;

describe('Script Tests', function() {
    let document, window, sessionStorage;

    beforeEach(() => {
        const dom = new JSDOM(`
            <!doctype html>
            <html>
                <body>
                    <input id="username" value="testUser" />
                    <input id="password" value="testPass" />
                    <button onclick="submitForm()">Submit</button>
                </body>
            </html>
        `);

        document = dom.window.document;
        window = dom.window;
        global.document = document;
        global.window = window;

        // Mock sessionStorage
        sessionStorage = {};
        sinon.stub(window.sessionStorage, 'setItem').callsFake((key, value) => {
            sessionStorage[key] = value;
        });
    });

    afterEach(() => {
        sinon.restore();
    });

    it('should store username and password in sessionStorage on form submission', () => {
        require('../JavaScript/script'); // Adjust the path to script.js if needed

        // Simulate form submission
        document.querySelector('button').click();
        
        // Check session storage values
        expect(sessionStorage.username).to.equal('testUser');
        expect(sessionStorage.password).to.equal('testPass');
    });
});
