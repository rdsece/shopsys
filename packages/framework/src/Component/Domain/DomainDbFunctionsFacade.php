<?php

namespace Shopsys\FrameworkBundle\Component\Domain;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class DomainDbFunctionsFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(EntityManagerInterface $em, Domain $domain)
    {
        $this->em = $em;
        $this->domain = $domain;
    }

    public function createDomainDbFunctions()
    {
        $this->createDomainIdsByLocaleFunction();
        $this->createLocaleByDomainIdFunction();
    }

    protected function createDomainIdsByLocaleFunction()
    {
        $domainsIdsByLocale = [];
        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            $domainsIdsByLocale[$domainConfig->getLocale()][] = $domainConfig->getId();
        }

        $domainIdsByLocaleSqlClauses = [];
        foreach ($domainsIdsByLocale as $locale => $domainIds) {
            $sql = 'WHEN locale = \'' . $locale . '\' THEN ';
            foreach ($domainIds as $domainId) {
                $sql .= ' RETURN NEXT ' . $domainId . ';';
            }
            $domainIdsByLocaleSqlClauses[] = $sql;
        }

        $query = $this->em->createNativeQuery(
            'CREATE OR REPLACE FUNCTION get_domain_ids_by_locale(locale text) RETURNS SETOF integer AS $$
            BEGIN
                CASE
                    ' . implode("\n", $domainIdsByLocaleSqlClauses) . '
                    ELSE RETURN;
                END CASE;
            END
            $$ LANGUAGE plpgsql IMMUTABLE;',
            new ResultSetMapping()
        );

        return $query->execute();
    }

    protected function createLocaleByDomainIdFunction()
    {
        $localeByDomainIdSqlClauses = [];
        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            $localeByDomainIdSqlClauses[] =
                'WHEN domain_id = ' . $domainConfig->getId()
                . ' THEN RETURN \'' . $domainConfig->getLocale() . '\';';
        }

        $query = $this->em->createNativeQuery(
            'CREATE OR REPLACE FUNCTION get_domain_locale(domain_id integer) RETURNS text AS $$
            BEGIN
                CASE
                    ' . implode("\n", $localeByDomainIdSqlClauses) . '
                    ELSE RAISE EXCEPTION \'Domain with ID % does not exists\', domain_id;
                END CASE;
            END
            $$ LANGUAGE plpgsql IMMUTABLE;',
            new ResultSetMapping()
        );

        return $query->execute();
    }
}
