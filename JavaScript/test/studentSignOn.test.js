const chai = require('chai');
const expect = chai.expect;
const sinon = require('sinon');
const mysql = require('mysql2/promise');
const studentSignOn = require('../JavaScript/studentSignOn'); // Adjust path if necessary

describe('studentSignOn Tests', function() {
    let dbMock;

    beforeEach(() => {
        // Mock database connection and query result
        dbMock = sinon.stub(mysql, 'createPool').returns({
            execute: sinon.stub().resolves([[{ id: 1, username: 'testUser', password: 'testPass' }]])
        });
    });

    afterEach(() => {
        sinon.restore();
    });

    it('should allow a student to sign on with valid credentials', async () => {
        const result = await studentSignOn.signOn('testUser', 'testPass');

        // Validate the query was executed
        const dbInstance = dbMock();
        expect(dbInstance.execute.calledOnce).to.be.true;

        // Check if the result has correct username and id
        expect(result).to.have.property('id', 1);
        expect(result).to.have.property('username', 'testUser');
    });

    it('should fail sign-on with invalid credentials', async () => {
        dbMock().execute.resolves([[]]); // Simulate no results for invalid credentials

        try {
            await studentSignOn.signOn('wrongUser', 'wrongPass');
        } catch (error) {
            expect(error.message).to.equal('Invalid username or password');
        }
    });
});
