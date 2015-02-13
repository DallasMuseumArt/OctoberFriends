#GITUSERNAME=username
#DATABASE_USERNAME="root"
#DATABASE_PASSWORD="test"
#DATABASE_NAME="october_db"

php := $(shell { type php; } 2>/dev/null)
composer := $(shell { type composer; } 2>/dev/null)
git := $(shell { type git; } 2>/dev/null)


default: octobercms rainlabusers rainlabgoogle octoberfriends friendstheme friendsrecommendation

.PHONY: check
check:
ifndef GITUSERNAME
	@echo "Set your github username first"; exit 2
endif
ifndef DATABASE_USERNAME
	@echo "Set your database username first"; exit 2
endif
ifndef DATABASE_PASSWORD 
	@echo "Set your database password first"; exit 2
endif
ifndef DATABASE_NAME
	@echo "Set your database name first"; exit 2
endif
ifndef php
	@echo "Install PHP first"; exit 2
endif
ifndef composer
	@echo "Install composer first"; exit 2
endif
ifndef git
	@echo "Install git first"; exit 2
endif

october/plugins/rainlab:
	mkdir $@

october/plugins/dma:
	mkdir $@

.PHONY: octobercms
octobercms: check
	composer create-project october/october october dev-master; \
	cd october; \
	sed -i "s/'database'  => 'database',/'database'  => '$(DATABASE_NAME)',/g" config/database.php; \
	sed -i "s/'password'  => '',/'password'  => '$(DATABASE_PASSWORD)',/g" config/database.php; \
	sed -i "s/'username'  => 'root',/'username'  => '$(DATABASE_USERNAME)',/g" config/database.php; \
	php artisan october:up

.PHONY: rainlabusers
rainlabusers: october/plugins/rainlab
	cd october/plugins/rainlab; \
	git clone https://$(GITUSERNAME)@github.com/rainlab/user-plugin.git user; \
	cd user; \
	composer install; \
	cd ../../../; \
	php artisan october:up

.PHONY: rainlabgoogle
rainlabgoogle: rainlabusers
	cd october/plugins/rainlab; \
	git clone https://$(GITUSERNAME)@github.com/rainlab/googleanalytics-plugin.git googleanalytics; \
	cd googleanalytics; \
	composer install; \
	cd ../../../; \
	php artisan october:up


.PHONY: octoberfriends
octoberfriends: october/plugins/dma
	cd october/plugins/dma; \
	git clone https://$(GITUSERNAME)@github.com/artsmia/OctoberFriends.git friends; \
	cd friends; \
	composer install; \
	cd ../../../; \
	php artisan october:up

.PHONY: friendstheme
friendstheme: octoberfriends
	cd october/themes; \
	git clone https://$(GITUSERNAME)@github.com/artsmia/OctoberFriendsTheme.git; \
	cd ../../../; \

.PHONY: friendsrecommendation
friendsrecommendation: octoberfriends
	cd october/plugins/dma; \
	git clone https://$(GITUSERNAME)@github.com/artsmia/FriendsRecommendations.git recommendations; \
	cd recommendations; \
	composer install; \
	cd ../../../; \
	php artisan october:up

