# Build & Run

1. Navigate to this directory (where this README and the `Makefile` reside).
2. Run `make up` to start the containers.
   This will automatically create the required network (`symbiota-network`) if it does not already exist, and then launch the stack using Podman Compose (or Docker Compose if you set `COMPOSE=docker-compose`).
3. The containers will be built and started automatically.

**Note:**
The Symbiota code is mounted from your local directory instead of being pulled from a repository. You can import the database using a backup file, or through the patches used by Symbiota.

---

# MySQL Container Setup

1. In the `docker-compose.yaml` file, ensure you have a bind mount from your local directory that stores the SQL backups into the MySQL container. If you have another way to import your schema, skip to step 3.
2. Run bash in the `symbiota-db` container, and use the following command to import the schema:
   ```
   mysql -u root --password=password symbiota < <backup file name>
   ```
3. Start the MySQL daemon using:
   ```
   mysql -u root -p
   ```
   Enter the password as `password` when prompted.
4. Run the following commands to create Symbiota users:
   ```
   CREATE USER 'symbiota-r'@'%' IDENTIFIED BY 'symbiota-r-pass';
   GRANT SELECT ON symbiota.* TO 'symbiota-r'@'%';

   CREATE USER 'symbiota-rw'@'%' IDENTIFIED BY 'symbiota-rw-pass';
   GRANT SELECT, INSERT, UPDATE, DELETE ON symbiota.* TO 'symbiota-rw'@'%';

   FLUSH PRIVILEGES;
   ```

---

# Stopping the Containers

To stop and remove the containers, run:
```
make down
```

---

**Tip:**
If you want to use Docker Compose instead of Podman Compose, run:
```
make up COMPOSE=docker-compose
```
or set `COMPOSE=docker-compose` in the `Makefile`.