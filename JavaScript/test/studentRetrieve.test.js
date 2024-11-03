const chai = require('chai');
const expect = chai.expect;
const sinon = require('sinon');
const mysql = require('mysql2/promise');
const studentRetrieve = require('../JavaScript/studentRetrieve'); // Adjust path if necessary

describe('studentRetrieve Tests', function() {
    let dbMock;

    beforeEach(() => {
        // Mock database connection
        dbMock = sinon.stub(mysql, 'createPool').returns({
            execute: sinon.stub().resolves([[
                {
                    email: 'test@student.com',
                    redBlackNum: 5,
                    redBlackHigh: 10,
                    redBlackLow: 2,
                    redBlackGPA: 3.7
                }
            ]])
        });
    });

    afterEach(() => {
        sinon.restore();
    });

    it('should retrieve and display correct student information', async () => {
        const result = await studentRetrieve.displayInfo('John', 'Doe');

        // Verify that the query was called
        const dbInstance = dbMock();
        expect(dbInstance.execute.calledOnce).to.be.true;
        
        // Verify returned data structure and values
        expect(result).to.have.property('email', 'test@student.com');
        expect(result).to.have.property('redBlackNum', 5);
        expect(result).to.have.property('redBlackHigh', 10);
        expect(result).to.have.property('redBlackLow', 2);
        expect(result).to.have.property('redBlackGPA', 3.7);
    });
});
