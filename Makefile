.PHONY: up reset stop status classic block

up:
	bash tests/bin/package.sh work/manual-lab/candidate.zip
	docker compose up -d --wait database wordpress
	docker compose run --rm setup

reset:
	docker compose down --volumes
	$(MAKE) up

stop:
	docker compose stop

status:
	docker compose ps

classic:
	docker compose run --rm -e WPI_MANUAL_MODE=classic setup

block:
	docker compose run --rm -e WPI_MANUAL_MODE=block setup
