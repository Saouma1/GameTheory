const chai = require('chai');
const expect = chai.expect;
const sinon = require('sinon');
const mysql = require('mysql2/promise');
const teamRetrieve = require('../JavaScript/teamRetrieve'); // Adjust path if necessary

describe('teamRetrieve Tests', function() {
    let dbMock;

    beforeEach(() => {
        // Mock database connection and query result
        dbMock = sinon.stub(mysql, 'createPool').returns({
            execute: sinon.stub().resolves([[
                { teamId: 1, teamName: 'Team A', score: 50 },
                { teamId: 1, teamName: 'Team A', score: 75 }
            ]])
        });
    });

    afterEach(() => {
        sinon.restore();
    });

    it('should retrieve team data based on team ID', async () => {
        const result = await teamRetrieve.getTeamData(1);

        // Validate the query was executed
        const dbInstance = dbMock();
        expect(dbInstance.execute.calledOnce).to.be.true;

        // Check that result contains the team data
        expect(result).to.be.an('array').that.has.lengthOf(2);
        expect(result[0]).to.have.property('teamName', 'Team A');
        expect(result[1]).to.have.property('score', 75);
    });

    it('should handle no data found for team ID', async () => {
        dbMock().execute.resolves([[]]); // Simulate no team data

        const result = await teamRetrieve.getTeamData(999);
        expect(result).to.be.an('array').that.is.empty;
    });
});
