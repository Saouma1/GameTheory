const chai = require('chai');
const expect = chai.expect;
const sinon = require('sinon');
const { createServer } = require('http');
const io = require('socket.io-client');
const mysql = require('mysql2/promise');
const server = require('../server'); // Adjust path if necessary

describe('Server Socket Tests', function() {
    let httpServer, clientSocket, dbMock;

    before(async function() {
        httpServer = createServer(server);
        httpServer.listen(3000);
        
        // Mock database connection pool
        dbMock = sinon.stub(mysql, 'createPool').returns({
            execute: sinon.stub().resolves([{}])  // Mock the execute method to return empty data
        });

        clientSocket = io('http://localhost:3000');
    });

    after(async function() {
        clientSocket.close();
        httpServer.close();
        dbMock.restore();
    });

    it('should connect to the server', (done) => {
        clientSocket.on('connect', () => {
            expect(clientSocket.connected).to.be.true;
            done();
        });
    });

    it('should emit and store a chat message', (done) => {
        const testMessage = "Hello, world!";
        
        clientSocket.emit('chat message', testMessage);
        clientSocket.on('chat message', (msg) => {
            expect(msg).to.equal(testMessage);

            // Verify DB interaction
            const dbInstance = dbMock();
            expect(dbInstance.execute.calledOnce).to.be.true;
            expect(dbInstance.execute.firstCall.args[0]).to.include('INSERT INTO chat_messages');
            expect(dbInstance.execute.firstCall.args[1]).to.include(testMessage);

            done();
        });
    });
});
